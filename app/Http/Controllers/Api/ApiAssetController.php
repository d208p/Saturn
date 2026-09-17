<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Holding;
use App\Models\Transaction;
use Illuminate\Http\Request;

class ApiAssetController extends Controller
{
    public function show(Request $request, Asset $asset)
    {
        $user = $request->user();

        // Retrieve user's holding for this asset
        $holding = Holding::where('user_id', $user->id)
            ->where('asset_id', $asset->id)
            ->first();

        // Calculate holding metrics
        $unitsOwned = $holding ? (float) $holding->shares_owned : 0;
        $totalInvested = $holding ? (float) $holding->total_invested : 0;
        $sharePrice = (float) $asset->share_price;
        $currentValue = $unitsOwned * $sharePrice;
        $avgPurchasePrice = $unitsOwned > 0 ? ($totalInvested / $unitsOwned) : 0;
        $unrealizedGain = $currentValue - $totalInvested;

        // Fetch recent distributions
        $distributions = Transaction::where('asset_id', $asset->id)
            ->where('type', 'distribution')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get()
            ->map(function ($dist) {
                return [
                    'date' => $dist->created_at->format('M d, Y'),
                    'type' => 'Quarterly distribution',
                    'amount_per_share' => (float) ($dist->amount_per_share ?? 0),
                ];
            });

        return response()->json([
            'asset' => [
                'id' => $asset->id,
                'title' => $asset->title,
                'location' => $asset->location ?? 'Global',
                'category' => $asset->category ?? 'Real Estate',
                'description' => $asset->description ?? '',
                'share_price' => $sharePrice,
                'total_valuation' => (float) ($asset->total_valuation ?? 0),
                'target_yield' => (float) ($asset->target_yield ?? 0),
                'total_return' => (float) ($asset->total_return ?? 0),
                'revenue_ttm' => (float) ($asset->revenue_ttm ?? 0),
                'occupancy_rate' => (float) ($asset->occupancy_rate ?? 0),
                'noi' => (float) ($asset->noi ?? 0),
                'available_shares' => (int) ($asset->available_shares ?? 0),
                'is_favorited' => (bool) ($asset->is_favorited ?? false),
            ],
            'holding' => [
                'units_owned' => $unitsOwned,
                'current_value' => $currentValue,
                'avg_purchase_price' => $avgPurchasePrice,
                'unrealized_gain' => $unrealizedGain,
            ],
            'distributions' => $distributions,
        ]);
    }
}