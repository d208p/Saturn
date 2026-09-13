<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\User;
use App\Models\Holding;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $stats = [
            'total_users' => User::count(),
            'total_assets' => Asset::count(),
            'total_volume' => Transaction::sum('amount'),
        ];

        $assets = Asset::latest()->get();

        return view('admin.admin', compact('stats', 'assets'));
    }

    public function distributeProfit(Request $request)
{
    $request->validate([
        'asset_id' => 'required|exists:assets,id',
        'total_amount' => 'required|numeric|min:0.01'
    ]);

    $asset = Asset::findOrFail($request->asset_id);
    $totalProfit = (float) $request->total_amount;

    // 1. Calculate how many shares are actually held by investors
    $circulatingShares = $asset->total_shares - $asset->available_shares;

    if ($circulatingShares <= 0) {
        return back()->with('success', 'Distribution failed: No users currently own shares of this asset.');
    }

    // 2. Calculate the dividend/profit per individual share
    $profitPerShare = $totalProfit / $circulatingShares;

    // 3. Get all holdings for this asset where users actually own shares
    // Note: depending on your DB structure, this might be 'shares_owned' or 'shares'
    $holdings = Holding::where('asset_id', $asset->id)
        ->where('shares_owned', '>', 0) 
        ->get();

    // 4. Process distributions safely inside a DB transaction
    DB::transaction(function () use ($holdings, $profitPerShare, $asset) {
        foreach ($holdings as $holding) {
            $userProfit = $holding->shares_owned * $profitPerShare;

            // Find the user and increment their available cash balance
            $user = User::find($holding->user_id);
            if ($user) {
                $user->increment('balance', $userProfit);

                // Create a transaction record so it shows up on their Incomes page
                Transaction::create([
                    'user_id' => $user->id,
                    'asset_id' => $asset->id,
                    'type' => 'income',
                    'amount' => $userProfit,
                    'shares' => $holding->shares_owned, // optional, for record keeping
                    'status' => 'completed',
                ]);
            }
        }
    });

    return back()->with('success', "Successfully distributed €" . number_format($totalProfit, 2) . " across shareholders of {$asset->title}.");
}

    public function storeAsset(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'category' => 'required|string|max:255',
            'description' => 'nullable|string',
            'total_valuation' => 'required|numeric|min:0',
            'total_shares' => 'required|integer|min:1',
            'share_price' => 'required|numeric|min:0',
        ]);

        $validated['available_shares'] = $validated['total_shares'];
        $validated['status'] = 'active';

        Asset::create($validated);

        return back()->with('success', 'New asset created successfully.');
    }
}