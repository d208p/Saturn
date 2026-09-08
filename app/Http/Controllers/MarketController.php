<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarketController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Fetch fundraising or new projects
        $newProjects = Asset::where('status', 'active')
            ->latest()
            ->get();

        // 2. Secondary market assets
        $secondaryAssets = Asset::where('status', 'active')
            ->where('available_shares', '>', 0)
            ->get();

        // 3. User's active listings (Empty fallback until models are created)
        $userListings = collect();

        // 4. User watchlist assets (Empty fallback until models are created)
        $watchlist = collect();

        return view('dashboard.market', compact(
            'newProjects',
            'secondaryAssets',
            'userListings',
            'watchlist'
        ));
    }
}