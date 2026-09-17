<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ApiPortfolioController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $holdings = $user->holdings()->with('asset')->get();

        $holdingsValue = (double) $holdings->sum(function ($holding) {
            return $holding->shares_owned * ($holding->asset->share_price ?? 0);
        });

        // 3. Cash & Total Portfolio Value
        $availableCash = (double) ($user->balance ?? 0);
        $portfolioValue = $holdingsValue + $availableCash;

        return response()->json([
            'portfolio_value' => $portfolioValue,
            'invested_total' => $holdingsValue,
            'available_cash' => $availableCash,
            'holdings' => $holdings,
        ]);
    }
}