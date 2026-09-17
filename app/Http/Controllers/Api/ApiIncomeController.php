<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ApiIncomeController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $currentYear = date('Y');

        // 1. Fetch total income for current year
        $incomeTransactions = $user->transactions()
            ->where('type', 'income')
            ->whereYear('created_at', $currentYear)
            ->get();

        $totalIncome = (float) $incomeTransactions->sum('amount');
        $averageMonthlyIncome = $incomeTransactions->count() > 0 
            ? $totalIncome / max((int) date('n'), 1) 
            : 0;

        // 2. Group income by month for chart
        $monthlyIncome = $user->transactions()
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(amount) as total')
            )
            ->where('type', 'income')
            ->whereYear('created_at', $currentYear)
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        $chartData = [];
        $maxMonthlyVal = max(array_values($monthlyIncome) ?: [1]);
        for ($m = 1; $m <= 12; $m++) {
            $val = (float) ($monthlyIncome[$m] ?? 0);
            $chartData[] = [
                'label' => strtoupper(date('M', mktime(0, 0, 0, $m, 1))),
                'value' => $val,
                'height' => $val > 0 ? round(($val / $maxMonthlyVal) * 100) : 0,
            ];
        }

        // 3. Upcoming distributions
        $upcomingDistributions = $user->holdings()
            ->with('asset')
            ->get()
            ->map(function ($holding) {
                return [
                    'asset_name' => $holding->asset->title ?? 'Asset',
                    'payout' => (float) ($holding->shares * ($holding->asset->yield_per_share ?? 0)),
                    'date' => 'Upcoming',
                ];
            });

        return response()->json([
            'balance' => (float) ($user->balance ?? 0),
            'total_income' => $totalIncome,
            'average_monthly_income' => $averageMonthlyIncome,
            'chart_data' => $chartData,
            'upcoming_distributions' => $upcomingDistributions,
        ]);
    }
}