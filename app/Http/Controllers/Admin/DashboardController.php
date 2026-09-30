<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Holding;
use App\Models\PlatformFee;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users'         => User::count(),
            'total_assets'        => Asset::count(),
            'total_volume'        => Transaction::sum('amount'),
            // Fee & Financial Metrics
            'total_fees_collected'=> PlatformFee::sum('amount'),
            'today_fees'          => PlatformFee::whereDate('created_at', today())->sum('amount'),
            'total_user_balances' => User::sum('balance'),
        ];

        // Fetch recent fee logs with relationships
        $recentFees = PlatformFee::with(['user', 'asset'])
            ->latest()
            ->take(10)
            ->get();

        $assets = Asset::latest()->get();

        return view('admin.admin', compact('stats', 'assets', 'recentFees'));
    }

    public function distributeProfit(Request $request)
    {
        $request->validate([
            'asset_id'     => 'required|exists:assets,id',
            'total_amount' => 'required|numeric|min:0.01',
        ]);

        $asset = Asset::findOrFail($request->asset_id);
        $totalProfit = (float) $request->total_amount;

        $circulatingShares = $asset->total_shares - $asset->available_shares;

        if ($circulatingShares <= 0) {
            return back()->with('error', 'Distribution failed: No users currently own shares of this asset.');
        }

        $profitPerShare = $totalProfit / $circulatingShares;

        $holdings = Holding::where('asset_id', $asset->id)
            ->where('shares_owned', '>', 0)
            ->get();

        DB::transaction(function () use ($holdings, $profitPerShare, $asset) {
            foreach ($holdings as $holding) {
                $userProfit = $holding->shares_owned * $profitPerShare;

                $user = User::find($holding->user_id);
                if ($user) {
                    $user->increment('balance', $userProfit);

                    Transaction::create([
                        'user_id'  => $user->id,
                        'asset_id' => $asset->id,
                        'type'     => 'income',
                        'amount'   => $userProfit,
                        'shares'   => $holding->shares_owned,
                        'status'   => 'completed',
                    ]);
                }
            }
        });

        return back()->with('success', "Successfully distributed €" . number_format($totalProfit, 2) . " across shareholders of {$asset->title}.");
    }

    public function storeAsset(Request $request)
    {
        $validated = $request->validate([
            'title'           => 'required|string|max:255',
            'category'        => 'required|string|max:255',
            'description'     => 'nullable|string',
            'total_valuation' => 'required|numeric|min:0',
            'total_shares'    => 'required|integer|min:1',
            'share_price'     => 'required|numeric|min:0',
        ]);

        $validated['available_shares'] = $validated['total_shares'];
        $validated['status'] = 'active';

        Asset::create($validated);

        return back()->with('success', 'New asset created successfully.');
    }
}