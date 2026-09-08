<?php

namespace App\Http\Controllers;

use App\Models\Holding;
use App\Models\Transaction;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Load holdings with asset details
        $holdings = Holding::where('user_id', $user->id)
            ->with('asset')
            ->get();

        // Calculate key metrics
        $investedValue = $holdings->sum(fn ($h) => $h->shares_owned * $h->asset->share_price);
        $totalInvestedCost = $holdings->sum('total_invested');
        $unrealizedGain = $investedValue - $totalInvestedCost;
        $availableCash = $user->balance ?? 0.00;
        $totalPortfolioValue = $investedValue + $availableCash;

        // Income / Distributions metrics
        $incomeReceived = Transaction::where('user_id', $user->id)
            ->where('type', 'distribution')
            ->where('status', 'completed')
            ->sum('amount');

        $thisMonthIncome = Transaction::where('user_id', $user->id)
            ->where('type', 'distribution')
            ->where('status', 'completed')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->sum('amount');

        // Market activity counts
        $activeOpportunitiesCount = Asset::where('status', 'active')->count();
        $secondaryPositionsCount = Asset::where('status', 'active')
            ->where('available_shares', '>', 0)
            ->count();

        // Portfolio yield calculation
        $portfolioYield = $investedValue > 0 
            ? (($incomeReceived / $investedValue) * 100) 
            : 0.0;

        return view('dashboard.dashboard', compact(
            'totalPortfolioValue',
            'investedValue',
            'unrealizedGain',
            'availableCash',
            'incomeReceived',
            'thisMonthIncome',
            'portfolioYield',
            'holdings',
            'activeOpportunitiesCount',
            'secondaryPositionsCount'
        ));
    }
}