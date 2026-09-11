<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Holding;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\SellOrder;
use Illuminate\Support\Facades\Auth;

class TradeController extends Controller
{
    public function show(Asset $asset)
    {
        $user = Auth::user();
        $holding = Holding::where('user_id', $user->id)
            ->where('asset_id', $asset->id)
            ->first();

        $userShares = $holding ? $holding->shares_owned : 0;

        return view('dashboard.trade', compact('asset', 'userShares'));
    }

    public function store(Request $request, Asset $asset)
    {
        $request->validate([
            'side' => 'required|in:buy,sell',
            'shares' => 'required|integer|min:1',
        ]);

        $side = $request->side;
        $shares = (int) $request->shares;
        $userId = Auth::id();

        return DB::transaction(function () use ($userId, $asset, $shares, $side) {
            $user = DB::table('users')->where('id', $userId)->lockForUpdate()->first();
            $lockedAsset = Asset::where('id', $asset->id)->lockForUpdate()->first();

            $totalAmount = $shares * $lockedAsset->share_price;

            if ($side === 'buy') {
                if ($lockedAsset->status !== 'active') {
                    return back()->withErrors(['trade' => 'This offering is not active.']);
                }

                if ($lockedAsset->available_shares < $shares) {
                    return back()->withErrors(['trade' => 'Not enough available shares remaining.']);
                }

                if ($user->balance < $totalAmount) {
                    return back()->withErrors(['trade' => 'Insufficient funds in account balance.']);
                }

                // Deduct balance and update asset shares
                DB::table('users')->where('id', $userId)->decrement('balance', $totalAmount);
                $lockedAsset->decrement('available_shares', $shares);

                if ($lockedAsset->available_shares === 0) {
                    $lockedAsset->update(['status' => 'funded']);
                }

                // Update holdings
                $holding = Holding::firstOrNew([
                    'user_id' => $userId,
                    'asset_id' => $lockedAsset->id,
                ]);

                $holding->shares_owned += $shares;
                $holding->total_invested += $totalAmount;
                $holding->save();

                // Log transaction
                Transaction::create([
                    'user_id' => $userId,
                    'asset_id' => $lockedAsset->id,
                    'type' => 'buy_equity',
                    'amount' => $totalAmount,
                    'shares' => $shares,
                    'status' => 'completed',
                ]);

                return redirect('/portfolio#holdings')->with('success', 'Shares purchased successfully.');
            } 
            
            if ($side === 'sell') {
                $holding = Holding::where('user_id', $userId)
                    ->where('asset_id', $lockedAsset->id)
                    ->lockForUpdate()
                    ->first();

                if (!$holding || $holding->shares_owned < $shares) {
                    return back()->withErrors(['trade' => 'You do not own enough shares to list for sale.']);
                }

                // 1. Reducem acțiunile disponibile ale vânzătorului (sunt blocate în ordinul de vânzare)
                $holding->decrement('shares_owned', $shares);

                // 2. Creăm ordinul pe piață secundară
                SellOrder::create([
                    'user_id' => $userId,
                    'asset_id' => $lockedAsset->id,
                    'shares' => $shares,
                    'price_per_share' => $lockedAsset->share_price,
                    'status' => 'active',
                ]);

                // 3. Înregistrăm intenția în jurnalul de tranzacții
                Transaction::create([
                    'user_id' => $userId,
                    'asset_id' => $lockedAsset->id,
                    'type' => 'sell_equity',
                    'amount' => $totalAmount,
                    'shares' => $shares,
                    'status' => 'listed', // Statusul devine "listed" în loc de "completed"
                ]);

                return redirect('/portfolio#holdings')->with('success', 'Shares successfully listed on the secondary market.');
            }
        });
    }
}