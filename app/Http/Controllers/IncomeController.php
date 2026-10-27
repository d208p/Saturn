<?php

namespace App\Http\Controllers;

use App\Models\PlatformFee;
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
            ->where('type', 'income')
            ->whereYear('created_at', date('Y'))
            ->get();

        // 2. Aggregate total and average income
        $totalIncome = $incomeTransactions->sum('amount');
        $averageMonthlyIncome = $incomeTransactions->count() > 0 
            ? $totalIncome / max(date('n'), 1) 
            : 0;

        // 3. Calculate total management taxes/distribution fees paid by the user this year
        $totalFees = PlatformFee::where('user_id', $user->id)
            ->where('source', 'management_tax')
            ->whereYear('created_at', date('Y'))
            ->sum('amount');

        // 4. Group income by month for the bar chart
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
        $maxMonthlyVal = max(array_values($monthlyIncome) ?: [1]);
        for ($m = 1; $m <= 12; $m++) {
            $val = $monthlyIncome[$m] ?? 0;
            $chartData[] = [
                'label' => strtoupper(date('M', mktime(0, 0, 0, $m, 1))),
                'value' => $val,
                'height' => $val > 0 ? round(($val / $maxMonthlyVal) * 100) : 0,
            ];
        }

        // 5. Upcoming distributions
        $upcomingDistributions = $user->holdings()
            ->with('asset')
            ->get()
            ->map(function ($holding) {
                $yieldPerShare = $holding->asset->yield_per_share ?? 0;
                return [
                    'asset_name' => $holding->asset->title ?? 'Asset',
                    'payout' => $holding->shares_owned * $yieldPerShare,
                    'date' => 'Upcoming',
                ];
            });

        return view('dashboard.income', compact(
            'user',
            'totalIncome',
            'averageMonthlyIncome',
            'totalFees',
            'chartData',
            'upcomingDistributions'
        ));
    }
}