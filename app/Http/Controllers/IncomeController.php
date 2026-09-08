<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class IncomeController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // 1. Fetch completed income transactions for the current year
        $incomeTransactions = $user->transactions()
            ->where('type', 'income') // or 'distribution'
            ->whereYear('created_at', date('Y'))
            ->get();

        // 2. Aggregate total and average income
        $totalIncome = $incomeTransactions->sum('amount');
        $averageMonthlyIncome = $incomeTransactions->count() > 0 
            ? $totalIncome / max(date('n'), 1) 
            : 0;

        // 3. Group income by month for the bar chart
        $monthlyIncome = $user->transactions()
            ->select(
                DB::raw('MONTH(created_at) as month'),
                DB::raw('SUM(amount) as total')
            )
            ->where('type', 'income')
            ->whereYear('created_at', date('Y'))
            ->groupBy('month')
            ->pluck('total', 'month')
            ->toArray();

        // Fill missing months with 0 for chart display
        $chartData = [];
        $maxMonthlyVal = max(array_values($monthlyIncome) ?: [1]); // Prevent division by zero
        for ($m = 1; $m <= 12; $m++) {
            $val = $monthlyIncome[$m] ?? 0;
            $chartData[] = [
                'label' => strtoupper(date('M', mktime(0, 0, 0, $m, 1))),
                'value' => $val,
                'height' => $val > 0 ? round(($val / $maxMonthlyVal) * 100) : 0,
            ];
        }

        // 4. Upcoming distributions (or scheduled dividend distributions)
        $upcomingDistributions = $user->holdings()
            ->with('asset')
            ->get()
            ->map(function ($holding) {
                return [
                    'asset_name' => $holding->asset->title ?? 'Asset',
                    'payout' => $holding->shares * ($holding->asset->yield_per_share ?? 0),
                    'date' => 'Upcoming',
                ];
            });

        return view('dashboard.income', compact(
            'user',
            'totalIncome',
            'averageMonthlyIncome',
            'chartData',
            'upcomingDistributions'
        ));
    }
}