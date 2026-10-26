<?php

namespace App\Http\Controllers;

use App\Models\SellOrder;
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

        // --- DYNAMIC RISK CALCULATIONS ---
        $totalHoldingsCount = $holdings->count();
        $uniqueCategories = $categoryHoldings->count();
        $uniqueCountries = $countryHoldings->count();

        $maxAssetShare = 0;
        if ($holdingsValue > 0) {
            $maxAssetValue = $holdings->max(function ($holding) {
                return $holding->shares_owned * ($holding->asset->share_price ?? 0);
            });
            $maxAssetShare = ($maxAssetValue / $holdingsValue) * 100;
        }

        // 1. Diversification Assessment
        if ($uniqueCategories >= 3 && $totalHoldingsCount >= 4) {
            $diversification = ['label' => 'Excellent', 'class' => 'positive'];
        } elseif ($uniqueCategories >= 2 || $totalHoldingsCount >= 2) {
            $diversification = ['label' => 'Good', 'class' => 'positive'];
        } else {
            $diversification = ['label' => 'Concentrated', 'class' => 'negative'];
        }

        // 2. Asset Concentration Assessment
        if ($maxAssetShare > 50) {
            $concentration = ['label' => 'High', 'class' => 'negative'];
        } elseif ($maxAssetShare > 25) {
            $concentration = ['label' => 'Moderate', 'class' => 'neutral'];
        } else {
            $concentration = ['label' => 'Low', 'class' => 'positive'];
        }

        // 3. Geographic Exposure Assessment
        if ($uniqueCountries >= 3) {
            $geographicRisk = ['label' => 'Well Distributed', 'class' => 'positive'];
        } elseif ($uniqueCountries == 2) {
            $geographicRisk = ['label' => 'Moderate', 'class' => 'neutral'];
        } else {
            $geographicRisk = ['label' => 'Single Region', 'class' => 'negative'];
        }

        // 4. Development Exposure (Weighted average based on admin-defined asset field)
        $weightedDevExposure = 0;
        if ($holdingsValue > 0) {
            $weightedDevExposure = $holdings->sum(function ($holding) {
                $assetValue = $holding->shares_owned * ($holding->asset->share_price ?? 0);
                return $assetValue * ($holding->asset->development_exposure ?? 0);
            }) / $holdingsValue;
        }

        if ($weightedDevExposure > 40) {
            $devExposure = ['label' => 'High (' . round($weightedDevExposure) . '%)', 'class' => 'negative'];
        } elseif ($weightedDevExposure > 15) {
            $devExposure = ['label' => 'Moderate (' . round($weightedDevExposure) . '%)', 'class' => 'neutral'];
        } else {
            $devExposure = ['label' => 'Low (' . round($weightedDevExposure) . '%)', 'class' => 'positive'];
        }

        // 5. Liquidity Assessment (Combining Admin Market Liquidity + Live Secondary Market Sell Orders)
        $userAssetIds = $holdings->pluck('asset_id');
        
        // Check how many assets have active secondary market listings
        $activeListingsCount = SellOrder::whereIn('asset_id', $userAssetIds)
            ->where('status', 'active')
            ->distinct('asset_id')
            ->count();

        // Calculate average admin market liquidity score across user holdings
        $adminLiquidityScore = 0;
        if ($totalHoldingsCount > 0) {
            $liquidityPoints = $holdings->sum(function ($holding) {
                $marketLiq = strtolower($holding->asset->market_liquidity ?? 'moderate');
                return match($marketLiq) {
                    'high', 'easy' => 3,
                    'moderate' => 2,
                    'low', 'hard' => 1,
                    default => 2,
                };
            });
            $adminLiquidityScore = $liquidityPoints / $totalHoldingsCount;
        }

        // Blend admin score (base market demand) with secondary market activity boost
        $totalLiquidityScore = $adminLiquidityScore + ($activeListingsCount > 0 ? 0.5 : 0);

        if ($totalLiquidityScore >= 2.5) {
            $liquidity = ['label' => 'High', 'class' => 'positive'];
        } elseif ($totalLiquidityScore >= 1.8) {
            $liquidity = ['label' => 'Moderate', 'class' => 'neutral'];
        } else {
            $liquidity = ['label' => 'Low', 'class' => 'negative'];
        }

        // Overall Risk Score (1.0 to 10.0 scale)
        $rawScore = 5.0 - ($uniqueCategories * 0.8) + ($maxAssetShare / 25);
        $riskScore = max(1.0, min(10.0, round($rawScore, 1)));
        
        $riskLabel = $riskScore >= 7.0 ? 'High Risk' : ($riskScore >= 4.0 ? 'Moderate' : 'Conservative');
        $riskBadgeClass = $riskScore >= 7.0 ? 'negative' : ($riskScore >= 4.0 ? 'neutral' : 'positive');

        return view('dashboard.portfolio', compact(
            'user',
            'holdings',
            'holdingsValue',
            'categoryHoldings',
            'geographicAllocation',
            'diversification',
            'concentration',
            'geographicRisk',
            'liquidity',
            'devExposure',
            'riskScore',
            'riskLabel',
            'riskBadgeClass'
        ));
    }
}