<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class PortfolioController extends Controller
{
    public function index()
    {
        $user = Auth::user()->load('holdings.asset');

        $holdings = $user->holdings
            ->filter(fn ($holding) => $holding->asset !== null && $holding->shares_owned > 0);

        $holdingsValue = $holdings->sum(function ($holding) {
            return $holding->shares_owned * ($holding->asset->share_price ?? 0);
        });

        // Allocation by asset category
        $categoryHoldings = $holdings->groupBy(function ($holding) {
            return $holding->asset->category ?: 'Other';
        });

        // Allocation by country
        $countryHoldings = $holdings->groupBy(function ($holding) {
            return $holding->asset->country ?: 'Unknown';
        });

        // Calculate the value and percentage for each country
        $geographicAllocation = $countryHoldings->map(function ($group, $country) use ($holdingsValue) {
            $value = $group->sum(function ($holding) {
                return $holding->shares_owned * ($holding->asset->share_price ?? 0);
            });

            $percentage = $holdingsValue > 0
                ? round(($value / $holdingsValue) * 100, 1)
                : 0;

            return [
                'country' => $country,
                'value' => $value,
                'percentage' => $percentage,
            ];
        })->sortByDesc('value')->values();

        return view('dashboard.portfolio', compact(
            'user',
            'holdings',
            'holdingsValue',
            'categoryHoldings',
            'geographicAllocation'
        ));
    }
}