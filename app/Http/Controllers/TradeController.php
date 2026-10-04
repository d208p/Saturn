<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Holding;
use App\Models\PlatformFee;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TradeController extends Controller
{
    /**
     * Platform fee charged when buying newly-issued shares directly from
     * the company (primary market). Distinct from the secondary market's
     * 3.3% fee, which SecondaryMarketController handles separately.
     */
    const PLATFORM_FEE_PERCENT = 0.049; // 4.9%

    public function show(Request $request, Asset $asset)
    {
        if ($request->wantsJson()) {
            return response()->json([
                'asset' => $asset,
                'platform_fee_percent' => self::PLATFORM_FEE_PERCENT * 100,
            ]);
        }

        return view('dashboard.trade', compact('asset'));
    }

    public function store(Request $request, Asset $asset)
    {
        $request->validate([
            'shares' => 'required|integer|min:1',
        ]);

        $shares = (int) $request->shares;
        $userId = Auth::id();

        $result = DB::transaction(function () use ($userId, $asset, $shares) {
            $user = DB::table('users')->where('id', $userId)->lockForUpdate()->first();
            $lockedAsset = Asset::where('id', $asset->id)->lockForUpdate()->first();

            // NOTE: MarketController's "New Projects" query filters on
            // status = 'funding', but this used to only accept 'active'.
            // Accepting both here so investing doesn't silently fail —
            // worth confirming which status your assets actually carry
            // while they're open for investment.
            if (!in_array($lockedAsset->status, ['funding', 'active'], true)) {
                return ['error' => 'This offering is not open for investment.'];
            }

            if ($lockedAsset->available_shares < $shares) {
                return ['error' => 'Not enough available shares remaining.'];
            }

            $subtotal = $shares * $lockedAsset->share_price;
            $platformFee = $subtotal * self::PLATFORM_FEE_PERCENT;
            $totalCost = $subtotal + $platformFee;

            if ($user->balance < $totalCost) {
                return ['error' => 'Insufficient funds in account balance.'];
            }

            // Buyer pays subtotal + fee; the asset's available shares only
            // move by the actual share count (the fee isn't "shares").
            DB::table('users')->where('id', $userId)->decrement('balance', $totalCost);
            $lockedAsset->decrement('available_shares', $shares);

            if ($lockedAsset->available_shares === 0) {
                $lockedAsset->update(['status' => 'funded']);
            }

            $holding = Holding::firstOrNew([
                'user_id' => $userId,
                'asset_id' => $lockedAsset->id,
            ]);
            $holding->shares_owned = ($holding->shares_owned ?? 0) + $shares;
            $holding->total_invested = ($holding->total_invested ?? 0) + $subtotal;
            $holding->save();

            Transaction::create([
                'user_id' => $userId,
                'asset_id' => $lockedAsset->id,
                'type' => 'buy_equity',
                'amount' => $totalCost,
                'shares' => $shares,
                'status' => 'completed',
            ]);

            // Same ledger pattern as SecondaryMarketController, so both
            // revenue streams show up together wherever fees are reported.
            PlatformFee::create([
                'user_id' => $userId,
                'asset_id' => $lockedAsset->id,
                'source' => 'primary_market',
                'amount' => $platformFee,
                'fee_percentage' => self::PLATFORM_FEE_PERCENT * 100,
            ]);

            return ['success' => true];
        });

        if (isset($result['error'])) {
            if ($request->wantsJson()) {
                return response()->json(['message' => $result['error']], 422);
            }
            return back()->withErrors(['trade' => $result['error']]);
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Shares purchased successfully.']);
        }

        return redirect('/portfolio#holdings')->with('success', 'Shares purchased successfully.');
    }
}