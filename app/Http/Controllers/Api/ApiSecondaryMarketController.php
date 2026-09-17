<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Holding;
use App\Models\SellOrder;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApiSecondaryMarketController extends Controller
{
    const PLATFORM_FEE_PERCENT = 0.033; // 3.3% fee

    // Get asset listings and ladder info
    public function showAssetListings(Asset $asset)
    {
        $userId = Auth::id();

        $listings = SellOrder::with('user:id,name')
            ->where('asset_id', $asset->id)
            ->where('status', 'active')
            ->where('user_id', '!=', $userId)
            ->orderBy('price_per_share', 'asc')
            ->get()
            ->map(function ($listing) {
                return [
                    'id' => $listing->id,
                    'seller_name' => $listing->user->name ?? 'User #' . $listing->user_id,
                    'price_per_share' => (float) $listing->price_per_share,
                    'shares' => (int) $listing->shares,
                    'subtotal' => (float) ($listing->shares * $listing->price_per_share),
                ];
            });

        $totalAvailableShares = $listings->sum('shares');
        $minPrice = $listings->min('price_per_share') ?? 0;

        return response()->json([
            'asset' => [
                'id' => $asset->id,
                'title' => $asset->title,
                'location' => $asset->location ?? 'Global',
            ],
            'total_available_shares' => $totalAvailableShares,
            'min_price' => (float) $minPrice,
            'listings' => $listings,
        ]);
    }

    // Execute multi-order market buy matching from cheapest to most expensive
    public function buyFromMarket(Request $request, Asset $asset)
    {
        $buyer = Auth::user();

        $request->validate([
            'shares' => 'required|integer|min:1',
        ]);

        $requestedShares = (int) $request->input('shares');

        $availableOrders = SellOrder::where('asset_id', $asset->id)
            ->where('status', 'active')
            ->where('user_id', '!=', $buyer->id)
            ->orderBy('price_per_share', 'asc')
            ->get();

        $totalSharesAvailable = $availableOrders->sum('shares');

        if ($requestedShares > $totalSharesAvailable) {
            return response()->json([
                'message' => "Only {$totalSharesAvailable} shares are available on the market."
            ], 422);
        }

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
            return response()->json([
                'message' => "Insufficient balance. Required total (including 3.3% fee): €" . number_format($totalCostWithFee, 2)
            ], 422);
        }

        DB::transaction(function () use ($buyer, $asset, $ordersToFulfill, $totalCostWithFee) {
            $buyer->decrement('balance', $totalCostWithFee);

            foreach ($ordersToFulfill as $item) {
                /** @var SellOrder $order */
                $order = $item['order'];
                $sharesTaken = $item['shares_to_take'];
                $payoutToSeller = $item['cost'];

                $seller = User::findOrFail($order->user_id);
                $seller->increment('balance', $payoutToSeller);

                if ($sharesTaken == $order->shares) {
                    $order->update(['shares' => 0, 'status' => 'filled']);
                } else {
                    $order->decrement('shares', $sharesTaken);
                }

                Transaction::create([
                    'user_id' => $seller->id,
                    'asset_id' => $asset->id,
                    'type' => 'sell_secondary',
                    'amount' => $payoutToSeller,
                    'shares' => $sharesTaken,
                    'status' => 'completed',
                ]);
            }

            $buyerHolding = Holding::firstOrCreate(
                ['user_id' => $buyer->id, 'asset_id' => $asset->id],
                ['shares_owned' => 0]
            );
            $buyerHolding->increment('shares_owned', array_sum(array_column($ordersToFulfill, 'shares_to_take')));

            Transaction::create([
                'user_id' => $buyer->id,
                'asset_id' => $asset->id,
                'type' => 'buy_secondary',
                'amount' => $totalCostWithFee,
                'shares' => array_sum(array_column($ordersToFulfill, 'shares_to_take')),
                'status' => 'completed',
            ]);
        });

        return response()->json([
            'message' => 'Purchase completed successfully!',
            'total_cost' => $totalCostWithFee,
        ]);
    }
}