<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\SellOrder;
use Illuminate\Http\Request;

class ApiMarketController extends Controller
{
    public function index(Request $request)
    {
        $userId = $request->user()->id;

        // 1. Primary Market Projects
        $newProjects = Asset::where('status', 'active')
            ->latest()
            ->get();

        // 2. Secondary Market Assets
        $secondaryAssets = Asset::whereHas('sellOrders', function ($query) use ($userId) {
                $query->where('status', 'active')
                      ->where('user_id', '!=', $userId);
            })
            ->withCount(['sellOrders as active_listings_count' => function ($query) use ($userId) {
                $query->where('status', 'active')->where('user_id', '!=', $userId);
            }])
            ->withSum(['sellOrders as total_secondary_shares' => function ($query) use ($userId) {
                $query->where('status', 'active')->where('user_id', '!=', $userId);
            }], 'shares')
            ->withMin(['sellOrders as min_price' => function ($query) use ($userId) {
                $query->where('status', 'active')->where('user_id', '!=', $userId);
            }], 'price_per_share')
            ->get();

        // 3. User Listings
        $userListings = SellOrder::with('asset')
            ->where('user_id', $userId)
            ->latest()
            ->get();

        // 4. Watchlist (placeholder collect or relationship)
        $watchlist = collect();

        return response()->json([
            'new_projects' => $newProjects,
            'secondary_assets' => $secondaryAssets,
            'user_listings' => $userListings,
            'watchlist' => $watchlist,
        ]);
    }
}