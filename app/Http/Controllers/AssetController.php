<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Holding;
use App\Models\Transaction;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class AssetController extends Controller
{
    public function show(Asset $asset)
    {
        $user = Auth::user();

        // User's holding in this asset
        $holding = Holding::where('user_id', $user->id)
            ->where('asset_id', $asset->id)
            ->first();

        // Position calculations
        $unitsOwned = $holding?->shares_owned ?? 0;
        $totalInvested = $holding?->total_invested ?? 0;

        $currentValue = $unitsOwned * $asset->share_price;

        $avgPurchasePrice = $unitsOwned > 0
            ? $totalInvested / $unitsOwned
            : 0;

        $unrealizedGain = $currentValue - $totalInvested;

        // Recent distributions
        $distributions = Transaction::where('asset_id', $asset->id)
            ->where('type', 'distribution')
            ->where('status', 'completed')
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard.asset', compact(
            'asset',
            'unitsOwned',
            'currentValue',
            'avgPurchasePrice',
            'unrealizedGain',
            'distributions'
        ));
    }
}