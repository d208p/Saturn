<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Holding;
use App\Models\SellOrder;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SecondaryMarketController extends Controller
{
    const PLATFORM_FEE_PERCENT = 0.033; // 3.3% comision platformă

    // Afișează pagina proiectului din piața secundară cu toate ordinele ordonate crescător după preț
    public function showAssetListings(Asset $asset)
    {
        $userId = Auth::id();

        $listings = SellOrder::with('user')
            ->where('asset_id', $asset->id)
            ->where('status', 'active')
            ->where('user_id', '!=', $userId)
            ->orderBy('price_per_share', 'asc') // Cel mai ieftin primul!
            ->get();

        $totalAvailableShares = $listings->sum('shares');
        $minPrice = $listings->min('price_per_share') ?? 0;

        return view('dashboard.secondary-asset', compact('asset', 'listings', 'totalAvailableShares', 'minPrice'));
    }

    // Cumparare automatizată stil Steam (de la cel mai ieftin ordin la cel mai scump)
    public function buyFromMarket(Request $request, Asset $asset)
    {
        $buyer = Auth::user();

        $request->validate([
            'shares' => 'required|integer|min:1',
        ]);

        $requestedShares = (int) $request->input('shares');

        // Preluăm ordinele active ordonate după cel mai mic preț
        $availableOrders = SellOrder::where('asset_id', $asset->id)
            ->where('status', 'active')
            ->where('user_id', '!=', $buyer->id)
            ->orderBy('price_per_share', 'asc')
            ->get();

        $totalSharesAvailable = $availableOrders->sum('shares');

        if ($requestedShares > $totalSharesAvailable) {
            return back()->withErrors(['buy' => "Sunt disponibile doar {$totalSharesAvailable} acțiuni pe piață."]);
        }

        // Calculăm costul estimat și verificăm balanța cumpărătorului
        $remainingSharesToProcess = $requestedShares;
        $totalSubtotal = 0;
        $ordersToFulfill = [];

        foreach ($availableOrders as $order) {
            if ($remainingSharesToProcess <= 0) break;

            $takeShares = min($remainingSharesToProcess, $order->shares);
            $costForThisChunk = $takeShares * $order->price_per_share;

            $totalSubtotal += $costForThisChunk;
            $ordersToFulfill[] = [
                'order' => $order,
                'shares_to_take' => $takeShares,
                'cost' => $costForThisChunk,
            ];

            $remainingSharesToProcess -= $takeShares;
        }

        $platformFee = $totalSubtotal * self::PLATFORM_FEE_PERCENT;
        $totalCostWithFee = $totalSubtotal + $platformFee;

        if ($buyer->balance < $totalCostWithFee) {
            return back()->withErrors([
                'buy' => "Fonduri insuficiente. Totalul necesar (inclusiv comision 3.3%): €" . number_format($totalCostWithFee, 2)
            ]);
        }

        // Executăm achiziția în tranzacție DB
        DB::transaction(function () use ($buyer, $asset, $ordersToFulfill, $totalCostWithFee, $platformFee) {
            // 1. Scădem totalul (subtotal + taxă) din contul cumpărătorului
            $buyer->decrement('balance', $totalCostWithFee);

            // 2. Procesăm fiecare ordin din ladder
            foreach ($ordersToFulfill as $item) {
                /** @var SellOrder $order */
                $order = $item['order'];
                $sharesTaken = $item['shares_to_take'];
                $payoutToSeller = $item['cost']; // Vânzătorul primește valoarea acțiunilor sale

                // Transferăm banii către vânzător
                $seller = User::findOrFail($order->user_id);
                $seller->increment('balance', $payoutToSeller);

                // Scădem stocul din ordin sau îl închidem
                if ($sharesTaken == $order->shares) {
                    $order->update(['shares' => 0, 'status' => 'filled']);
                } else {
                    $order->decrement('shares', $sharesTaken);
                }

                // Tranzacție vânzător
                Transaction::create([
                    'user_id' => $seller->id,
                    'asset_id' => $asset->id,
                    'type' => 'sell_secondary',
                    'amount' => $payoutToSeller,
                    'shares' => $sharesTaken,
                    'status' => 'completed',
                ]);
            }

            // 3. Adăugăm acțiunile cumpărate în contul cumpărătorului
            $buyerHolding = Holding::firstOrCreate(
                ['user_id' => $buyer->id, 'asset_id' => $asset->id],
                ['shares_owned' => 0]
            );
            $buyerHolding->increment('shares_owned', array_sum(array_column($ordersToFulfill, 'shares_to_take')));

            // 4. Tranzacție cumpărător
            Transaction::create([
                'user_id' => $buyer->id,
                'asset_id' => $asset->id,
                'type' => 'buy_secondary',
                'amount' => $totalCostWithFee,
                'shares' => array_sum(array_column($ordersToFulfill, 'shares_to_take')),
                'status' => 'completed',
            ]);
        });

        return redirect()->route('market')->with('success', 'Achiziție finalizată cu succes!');
    }
}