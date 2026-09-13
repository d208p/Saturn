<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\SellOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MarketController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // 1. Primary Market: Proiecte noi în finanțare
        $newProjects = Asset::where('status', 'funding')
            ->latest()
            ->get();

        // 2. Secondary Market: Asset-uri grupate stil Steam Market
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

        // 3. Listings-urile proprii ale utilizatorului
        $userListings = SellOrder::with('asset')
            ->where('user_id', $userId)
            ->latest()
            ->get();

        // 4. Watchlist
        $watchlist = collect();

        return view('dashboard.market', compact(
            'newProjects',
            'secondaryAssets',
            'userListings',
            'watchlist'
        ));
    }
}