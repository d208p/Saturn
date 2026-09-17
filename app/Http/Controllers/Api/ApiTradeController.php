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

class ApiTradeController extends Controller
{
    public function show(Asset $asset)
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
                'available_shares' => (int) $asset->available_shares,
                'status' => $asset->status,
            ],
            'user_shares' => $userShares,
        ]);
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

        try {
            return DB::transaction(function () use ($userId, $asset, $shares, $side) {
                $user = DB::table('users')->where('id', $userId)->lockForUpdate()->first();
                $lockedAsset = Asset::where('id', $asset->id)->lockForUpdate()->first();

                $totalAmount = $shares * $lockedAsset->share_price;

                if ($side === 'buy') {
                    if ($lockedAsset->status !== 'active') {
                        return response()->json(['message' => 'This offering is not active.'], 422);
                    }

                    if ($lockedAsset->available_shares < $shares) {
                        return response()->json(['message' => 'Not enough available shares remaining.'], 422);
                    }

                    if ($user->balance < $totalAmount) {
                        return response()->json(['message' => 'Insufficient funds in account balance.'], 422);
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

                    return response()->json([
                        'message' => 'Shares purchased successfully.',
                    ]);
                }

                if ($side === 'sell') {
                    $holding = Holding::where('user_id', $userId)
                        ->where('asset_id', $lockedAsset->id)
                        ->lockForUpdate()
                        ->first();

                    if (!$holding || $holding->shares_owned < $shares) {
                        return response()->json(['message' => 'You do not own enough shares to list for sale.'], 422);
                    }

                    // 1. Lock user's available shares
                    $holding->decrement('shares_owned', $shares);

                    // 2. Create secondary market order
                    SellOrder::create([
                        'user_id' => $userId,
                        'asset_id' => $lockedAsset->id,
                        'shares' => $shares,
                        'price_per_share' => $lockedAsset->share_price,
                        'status' => 'active',
                    ]);

                    // 3. Log transaction
                    Transaction::create([
                        'user_id' => $userId,
                        'asset_id' => $lockedAsset->id,
                        'type' => 'sell_equity',
                        'amount' => $totalAmount,
                        'shares' => $shares,
                        'status' => 'listed',
                    ]);

                    return response()->json([
                        'message' => 'Shares successfully listed on the secondary market.',
                    ]);
                }
            });
        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage() ?: 'Trade execution failed.',
            ], 500);
        }
    }
}