<?php

namespace App\Http\Controllers;

use App\Models\Holding;
use App\Models\Transaction;
use App\Models\Asset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

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
        $investedValue = $holdings->sum(fn ($h) => $h->shares_owned * ($h->asset->share_price ?? 0));
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

        // ==========================================
        // GENERATE PERFORMANCE HISTORY FOR CHART
        // ==========================================
        $transactions = Transaction::where('user_id', $user->id)
            ->where('status', 'completed')
            ->orderBy('created_at', 'asc')
            ->get();

        $performanceHistory = [];

        if ($transactions->isNotEmpty()) {
            $currentCash = 0;
            $sharesHeld = [];
            
            $groupedTransactions = $transactions->groupBy(fn($t) => $t->created_at->format('Y-m-d'));
            $startDate = $transactions->first()->created_at->copy()->startOfDay()->subDay();
            $endDate = now()->endOfDay();
            
            $period = CarbonPeriod::create($startDate, '1 day', $endDate);
            
            foreach ($period as $date) {
                $dateStr = $date->format('Y-m-d');
                
                if (isset($groupedTransactions[$dateStr])) {
                    foreach ($groupedTransactions[$dateStr] as $tx) {
                        if (in_array($tx->type, ['deposit', 'distribution', 'income'])) {
                            $currentCash += $tx->amount;
                        } elseif ($tx->type === 'withdrawal') {
                            $currentCash -= $tx->amount;
                        } elseif (in_array($tx->type, ['trade', 'buy', 'investment'])) {
                            $currentCash -= $tx->amount;
                            if ($tx->asset_id) {
                                $sharesHeld[$tx->asset_id] = ($sharesHeld[$tx->asset_id] ?? 0) + ($tx->shares ?? 0);
                            }
                        } elseif ($tx->type === 'sell') {
                            $currentCash += $tx->amount;
                            if ($tx->asset_id) {
                                $sharesHeld[$tx->asset_id] = max(0, ($sharesHeld[$tx->asset_id] ?? 0) - ($tx->shares ?? 0));
                            }
                        }
                    }
                }
                
                $investedVal = 0;
                foreach ($sharesHeld as $assetId => $shares) {
                    $assetAsset = Asset::find($assetId);
                    if ($assetAsset) {
                        $investedVal += $shares * $assetAsset->share_price;
                    }
                }
                
                $totalVal = max(0, $currentCash) + $investedVal;
                $performanceHistory[] = [
                    'date' => $dateStr,
                    'value' => round($totalVal, 2)
                ];
            }
        }

        // Ensure the final data point exactly matches the current live portfolio value for today
        if (!empty($performanceHistory)) {
            $todayStr = now()->format('Y-m-d');
            $lastIndex = count($performanceHistory) - 1;
            if ($performanceHistory[$lastIndex]['date'] === $todayStr) {
                $performanceHistory[$lastIndex]['value'] = round($totalPortfolioValue, 2);
            } else {
                $performanceHistory[] = [
                    'date' => $todayStr,
                    'value' => round($totalPortfolioValue, 2)
                ];
            }
        } else {
            // Fallback: If no transactions yet, show a flat baseline for today and yesterday so the chart renders
            $performanceHistory = [
                ['date' => now()->subDay()->format('Y-m-d'), 'value' => round($totalPortfolioValue, 2)],
                ['date' => now()->format('Y-m-d'), 'value' => round($totalPortfolioValue, 2)]
            ];
        }

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
            'secondaryPositionsCount',
            'performanceHistory' // <-- Passed to view
        ));
    }
}