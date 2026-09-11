<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\SellOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MarketController extends Controller
{
    public function index()
    {
        $userId = Auth::id();

        // 1. Proiecte noi aflate în faza de finanțare (Primary Market)
        $newProjects = Asset::where('status', 'funding')
            ->latest()
            ->get();

        // 2. Secondary Market: Toate ordinele de vânzare active puse de ALȚI utilizatori
        $secondaryAssets = SellOrder::with(['asset', 'user'])
            ->where('status', 'active')
            ->where('user_id', '!=', $userId) // Oprim afișarea propriilor ordine la cumpărare
            ->latest()
            ->get();

        // 3. Listings-urile proprii ale utilizatorului conectat
        $userListings = SellOrder::with('asset')
            ->where('user_id', $userId)
            ->latest()
            ->get();

        // 4. Watchlist (Rămâne colecție goală temporar)
        $watchlist = collect();

        return view('dashboard.market', compact(
            'newProjects',
            'secondaryAssets',
            'userListings',
            'watchlist'
        ));
    }
}