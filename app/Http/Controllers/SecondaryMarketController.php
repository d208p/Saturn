<?php

namespace App\Http\Controllers;

use App\Models\Holding;
use App\Models\SellOrder;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SecondaryMarketController extends Controller
{
    public function show(SellOrder $sellOrder)
    {
        // Ne asigurăm că ordinul este activ și nu aparține utilizatorului curent
        if ($sellOrder->status !== 'active') {
            return redirect()->route('market')->with('error', 'This listing is no longer active.');
        }

        if ($sellOrder->user_id === Auth::id()) {
            return redirect()->route('market')->with('error', 'You cannot buy your own listing.');
        }

        $sellOrder->load(['asset', 'user']);

        return view('dashboard.secondary-buy', compact('sellOrder'));
    }

    public function buy(Request $request, SellOrder $sellOrder)
    {
        $request->validate([
            'shares' => 'required|integer|min:1',
        ]);

        $sharesToBuy = (int) $request->input('shares');
        $buyer = Auth::user();

        if ($sellOrder->status !== 'active') {
            return back()->withErrors(['buy' => 'This order is no longer available.']);
        }

        if ($sellOrder->user_id === $buyer->id) {
            return back()->withErrors(['buy' => 'You cannot buy your own listing.']);
        }

        if ($sharesToBuy > $sellOrder->shares) {
            return back()->withErrors(['buy' => 'You cannot buy more shares than available in this order.']);
        }

        $totalCost = $sharesToBuy * $sellOrder->price_per_share;

        if ($buyer->balance < $totalCost) {
            return back()->withErrors(['buy' => 'Insufficient balance to complete this purchase.']);
        }

        DB::transaction(function () use ($sellOrder, $sharesToBuy, $totalCost, $buyer) {
            $seller = User::findOrFail($sellOrder->user_id);

            // 1. Scădem banii cumpărătorului și adăugăm banii vânzătorului
            $buyer->decrement('balance', $totalCost);
            $seller->increment('balance', $totalCost);

            // 2. Transferăm acțiunile către cumpărător (Holding)
            $buyerHolding = Holding::firstOrCreate(
                ['user_id' => $buyer->id, 'asset_id' => $sellOrder->asset_id],
                ['shares_owned' => 0]
            );
            $buyerHolding->increment('shares_owned', $sharesToBuy);

            // 3. Actualizăm sau închidem SellOrder-ul
            if ($sharesToBuy === $sellOrder->shares) {
                $sellOrder->update(['shares' => 0, 'status' => 'filled']);
            } else {
                $sellOrder->decrement('shares', $sharesToBuy);
            }

            // 4. Înregistrăm tranzacțiile în jurnal pentru ambii utilizatori
            Transaction::create([
                'user_id' => $buyer->id,
                'asset_id' => $sellOrder->asset_id,
                'type' => 'buy_secondary',
                'amount' => $totalCost,
                'shares' => $sharesToBuy,
                'status' => 'completed',
            ]);

            Transaction::create([
                'user_id' => $seller->id,
                'asset_id' => $sellOrder->asset_id,
                'type' => 'sell_secondary',
                'amount' => $totalCost,
                'shares' => $sharesToBuy,
                'status' => 'completed',
            ]);
        });

        return redirect()->route('market')->with('success', 'Purchase completed successfully!');
    }
}