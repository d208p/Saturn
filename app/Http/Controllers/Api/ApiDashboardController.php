<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Asset;
use App\Models\Holding;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Carbon\CarbonPeriod;

class ApiDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // 1. Load holdings with asset details
        $holdings = Holding::where('user_id', $user->id)
            ->with('asset')
            ->get();

        // 2. Calculate key portfolio metrics
        $investedValue = $holdings->sum(
            fn ($holding) => $holding->shares_owned * ($holding->asset->share_price ?? 0)
        );

        $totalInvestedCost = $holdings->sum('total_invested');

        $unrealizedGain = $investedValue - $totalInvestedCost;

        $availableCash = $user->balance ?? 0.00;

        $totalPortfolioValue = $investedValue + $availableCash;

        // 3. Income / Distribution metrics
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

        // 4. Market activity counts
        $activeOpportunitiesCount = Asset::where('status', 'active')
            ->count();

        $secondaryPositionsCount = Asset::where('status', 'active')
            ->where('available_shares', '>', 0)
            ->count();

        // 5. Portfolio yield
        $portfolioYield = $investedValue > 0
            ? ($incomeReceived / $investedValue) * 100
            : 0.0;

        // 6. Performance history
        $transactions = Transaction::where('user_id', $user->id)
            ->where('status', 'completed')
            ->orderBy('created_at', 'asc')
            ->get();

        $performanceHistory = [];

        if ($transactions->isNotEmpty()) {

            $currentCash = 0;
            $sharesHeld = [];

            $groupedTransactions = $transactions->groupBy(
                fn ($transaction) => $transaction->created_at->format('Y-m-d')
            );

            $startDate = $transactions->first()
                ->created_at
                ->copy()
                ->startOfDay()
                ->subDay();

            $endDate = now()->endOfDay();

            $period = CarbonPeriod::create($startDate, '1 day', $endDate);

            // Load all relevant asset prices once instead of querying inside the loop
            $assetIds = $transactions
                ->pluck('asset_id')
                ->filter()
                ->unique();

            $assetPrices = Asset::whereIn('id', $assetIds)
                ->pluck('share_price', 'id');

            foreach ($period as $date) {

                $dateStr = $date->format('Y-m-d');

                // Process transactions for this day
                if (isset($groupedTransactions[$dateStr])) {

                    foreach ($groupedTransactions[$dateStr] as $tx) {

                        if (in_array($tx->type, ['deposit', 'distribution', 'income'])) {

                            $currentCash += $tx->amount;

                        } elseif ($tx->type === 'withdrawal') {

                            $currentCash -= $tx->amount;

                        } elseif (in_array($tx->type, ['trade', 'buy', 'investment'])) {

                            $currentCash -= $tx->amount;

                            if ($tx->asset_id) {
                                $sharesHeld[$tx->asset_id] =
                                    ($sharesHeld[$tx->asset_id] ?? 0) + ($tx->shares ?? 0);
                            }

                        } elseif ($tx->type === 'sell') {

                            $currentCash += $tx->amount;

                            if ($tx->asset_id) {
                                $sharesHeld[$tx->asset_id] = max(
                                    0,
                                    ($sharesHeld[$tx->asset_id] ?? 0) - ($tx->shares ?? 0)
                                );
                            }
                        }
                    }
                }

                // Calculate portfolio value for this day
                $investedVal = 0;

                foreach ($sharesHeld as $assetId => $shares) {

                    $sharePrice = $assetPrices[$assetId] ?? 0;

                    $investedVal += $shares * $sharePrice;
                }

                $totalVal = max(0, $currentCash) + $investedVal;

                $performanceHistory[] = [
                    'date' => $dateStr,
                    'value' => round($totalVal, 2),
                ];
            }
        }

        // 7. Ensure final data point matches current portfolio value
        $todayStr = now()->format('Y-m-d');

        if (!empty($performanceHistory)) {

            $lastIndex = count($performanceHistory) - 1;

            if ($performanceHistory[$lastIndex]['date'] === $todayStr) {

                $performanceHistory[$lastIndex]['value'] =
                    round($totalPortfolioValue, 2);

            } else {

                $performanceHistory[] = [
                    'date' => $todayStr,
                    'value' => round($totalPortfolioValue, 2),
                ];
            }

        } else {

            // Fallback: flat baseline for users without completed transactions
            $performanceHistory = [
                [
                    'date' => now()->subDay()->format('Y-m-d'),
                    'value' => round($totalPortfolioValue, 2),
                ],
                [
                    'date' => $todayStr,
                    'value' => round($totalPortfolioValue, 2),
                ],
            ];
        }

        // 8. Return dashboard data as JSON
        return response()->json([
            'portfolio' => [
                'total_value' => round($totalPortfolioValue, 2),
                'invested_value' => round($investedValue, 2),
                'invested_cost' => round($totalInvestedCost, 2),
                'unrealized_gain' => round($unrealizedGain, 2),
                'available_cash' => round($availableCash, 2),
                'portfolio_yield' => round($portfolioYield, 2),
            ],

            'income' => [
                'total_received' => round($incomeReceived, 2),
                'this_month' => round($thisMonthIncome, 2),
            ],

            'market' => [
                'active_opportunities' => $activeOpportunitiesCount,
                'secondary_positions' => $secondaryPositionsCount,
            ],

            'holdings' => $holdings,

            'performance_history' => $performanceHistory,
        ]);
    }
}
