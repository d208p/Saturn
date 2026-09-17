<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Holding;
use App\Models\SellOrder;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApiListingController extends Controller
{
    public function create(Asset $asset)
    {
        $userId = Auth::id();

        $holding = Holding::where('user_id', $userId)
            ->where('asset_id', $asset->id)
            ->first();

        $userShares = $holding ? (int) $holding->shares_owned : 0;

        return response()->json([
            'asset' => [
                'id' => $asset->id,
                'title' => $asset->title,
                'share_price' => (float) $asset->share_price,
            ],
            'user_shares' => $userShares,
        ]);
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
            $holding->decrement('shares_owned', $sharesToList);

            SellOrder::create([
                'user_id' => $userId,
                'asset_id' => $asset->id,
                'shares' => $sharesToList,
                'price_per_share' => $askingPrice,
                'status' => 'active',
            ]);

            Transaction::create([
                'user_id' => $userId,
                'asset_id' => $asset->id,
                'type' => 'sell_equity',
                'amount' => $sharesToList * $askingPrice,
                'shares' => $sharesToList,
                'status' => 'listed',
            ]);
        });

        return response()->json([
            'message' => 'Listing created successfully!',
        ]);
    }

    public function cancel(SellOrder $sellOrder)
    {
        $userId = Auth::id();

        if ($sellOrder->user_id !== $userId) {
            return response()->json(['message' => 'Unauthorized action.'], 403);
        }

        if ($sellOrder->status !== 'active') {
            return response()->json(['message' => 'Only active listings can be cancelled.'], 422);
        }

        DB::transaction(function () use ($sellOrder, $userId) {
            $holding = Holding::firstOrCreate(
                ['user_id' => $userId, 'asset_id' => $sellOrder->asset_id],
                ['shares_owned' => 0]
            );
            $holding->increment('shares_owned', $sellOrder->shares);

            $sellOrder->update(['status' => 'cancelled']);

            Transaction::create([
                'user_id' => $userId,
                'asset_id' => $sellOrder->asset_id,
                'type' => 'cancel_listing',
                'amount' => $sellOrder->shares * $sellOrder->price_per_share,
                'shares' => $sellOrder->shares,
                'status' => 'cancelled',
            ]);
        });

        return response()->json([
            'message' => 'Listing cancelled successfully. Shares returned to holding.',
        ]);
    }
}