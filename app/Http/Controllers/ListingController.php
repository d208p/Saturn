<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Holding;
use App\Models\SellOrder;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ListingController extends Controller
{
    public function create(Asset $asset)
    {
        $userId = Auth::id();

        // Preluăm câte acțiuni deține utilizatorul în acest asset
        $holding = Holding::where('user_id', $userId)
            ->where('asset_id', $asset->id)
            ->first();

        $userShares = $holding ? $holding->shares_owned : 0;

        if ($userShares < 1) {
            return redirect()->route('market')->with('error', 'Nu deții acțiuni la acest asset pentru a le putea scoate la vânzare.');
        }

        return view('dashboard.listings-create', compact('asset', 'userShares'));
    }

    public function store(Request $request, Asset $asset)
    {
        $userId = Auth::id();

        $holding = Holding::where('user_id', $userId)
            ->where('asset_id', $asset->id)
            ->first();

        $userShares = $holding ? $holding->shares_owned : 0;

        $request->validate([
            'shares' => ['required', 'integer', 'min:1', "max:{$userShares}"],
            'price_per_share' => ['required', 'numeric', 'min:0.01'],
        ]);

        $sharesToList = (int) $request->input('shares');
        $askingPrice = (float) $request->input('price_per_share');

        DB::transaction(function () use ($holding, $sharesToList, $askingPrice, $userId, $asset) {
            // 1. Scădem/Blocăm acțiunile din poziția utilizatorului
            $holding->decrement('shares_owned', $sharesToList);

            // 2. Creăm ordinul de vânzare în piața secundară
            SellOrder::create([
                'user_id' => $userId,
                'asset_id' => $asset->id,
                'shares' => $sharesToList,
                'price_per_share' => $askingPrice,
                'status' => 'active',
            ]);

            // 3. Înregistrăm în istoricul de tranzacții
            Transaction::create([
                'user_id' => $userId,
                'asset_id' => $asset->id,
                'type' => 'sell_equity',
                'amount' => $sharesToList * $askingPrice,
                'shares' => $sharesToList,
                'status' => 'listed',
            ]);
        });

        return redirect()->route('market')->with('success', 'Ordinul de vânzare a fost creat cu succes!');
    }

    public function cancel(SellOrder $sellOrder)
    {
        $userId = Auth::id();

        // Verificăm dacă ordinul aparține utilizatorului autentificat
        if ($sellOrder->user_id !== $userId) {
            return redirect()->route('market')->with('error', 'Nu ai permisiunea de a anula acest ordin.');
        }

        // Verificăm dacă ordinul este într-adevăr activ
        if ($sellOrder->status !== 'active') {
            return redirect()->route('market')->with('error', 'Doar ordinele active pot fi anulate.');
        }

        DB::transaction(function () use ($sellOrder, $userId) {
            // 1. Înapoiem acțiunile în poziția de holding a utilizatorului
            $holding = Holding::firstOrCreate(
                ['user_id' => $userId, 'asset_id' => $sellOrder->asset_id],
                ['shares_owned' => 0]
            );
            $holding->increment('shares_owned', $sellOrder->shares);

            // 2. Trecem statusul ordinului în 'cancelled'
            $sellOrder->update([
                'status' => 'cancelled',
            ]);

            // 3. Adăugăm o înregistrare în tranzacții pentru jurnalizare
            Transaction::create([
                'user_id' => $userId,
                'asset_id' => $sellOrder->asset_id,
                'type' => 'cancel_listing',
                'amount' => $sellOrder->shares * $sellOrder->price_per_share,
                'shares' => $sellOrder->shares,
                'status' => 'cancelled',
            ]);
        });

        return redirect()->route('market')->with('success', 'Ordinul de vânzare a fost anulat, iar acțiunile ți-au fost returnate.');
    }
}