<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Holding;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AssetController extends Controller
{
    public function show(Asset $asset)
    {
        $user = Auth::user();

        // Retrieve user's holding for this asset if it exists
        $holding = Holding::where('user_id', $user->id)
            ->where('asset_id', $asset->id)
            ->first();

        // Calculate holding metrics
        $unitsOwned = $holding ? $holding->shares_owned : 0;
        $totalInvested = $holding ? $holding->total_invested : 0;
        $currentValue = $unitsOwned * $asset->share_price;
        $avgPurchasePrice = $unitsOwned > 0 ? ($totalInvested / $unitsOwned) : 0;
        $unrealizedGain = $currentValue - $totalInvested;

        // Fetch recent distributions for this asset
        $distributions = Transaction::where('asset_id', $asset->id)
            ->where('type', 'distribution')
            ->orderBy('created_at', 'desc')
            ->take(5)
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