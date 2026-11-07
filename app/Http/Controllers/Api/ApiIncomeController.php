<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PlatformFee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiIncomeController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $currentYear = date('Y');

        // 1. Fetch income transactions for the current year
        $incomeTransactions = $user->transactions()
            ->where('type', 'income')
            ->whereYear('created_at', $currentYear)
            ->get();

        // 2. Calculate total and average income
        $totalIncome = (float) $incomeTransactions->sum('amount');

        $averageMonthlyIncome = $incomeTransactions->count() > 0
            ? $totalIncome / max((int) date('n'), 1)
            : 0;

        // 3. Calculate total management fees paid this year
        $totalFees = (float) PlatformFee::where('user_id', $user->id)
            ->where('source', 'management_tax')
            ->whereYear('created_at', $currentYear)
            ->sum('amount');

        // 4. Group income by month for the chart
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

        // Fill missing months with zero
        $chartData = [];

        $maxMonthlyVal = max(array_values($monthlyIncome) ?: [1]);

        for ($m = 1; $m <= 12; $m++) {
            $val = (float) ($monthlyIncome[$m] ?? 0);

            $chartData[] = [
                'label' => strtoupper(date('M', mktime(0, 0, 0, $m, 1))),
                'value' => $val,
                'height' => $val > 0
                    ? round(($val / $maxMonthlyVal) * 100)
                    : 0,
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
                    'payout' => (float) ($holding->shares_owned * $yieldPerShare),
                    'date' => 'Upcoming',
                ];
            })
            ->values();

        // 6. Return JSON response
        return response()->json([
            'balance' => (float) ($user->balance ?? 0),

            'total_income' => $totalIncome,
            'average_monthly_income' => round($averageMonthlyIncome, 2),
            'total_fees' => $totalFees,

            'chart_data' => $chartData,

            'upcoming_distributions' => $upcomingDistributions,
        ]);
    }
}
