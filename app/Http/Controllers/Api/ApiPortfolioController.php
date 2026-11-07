<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SellOrder;
use Illuminate\Http\Request;

class ApiPortfolioController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user()->load('holdings.asset');

        // 1. Load valid holdings
        $holdings = $user->holdings
            ->filter(fn ($holding) =>
                $holding->asset !== null &&
                $holding->shares_owned > 0
            )
            ->values();

        // 2. Calculate portfolio value
        $holdingsValue = $holdings->sum(function ($holding) {
            return $holding->shares_owned *
                ($holding->asset->share_price ?? 0);
        });

        $availableCash = (float) ($user->balance ?? 0);
        $portfolioValue = $holdingsValue + $availableCash;

        // 3. Allocation by asset category
        $categoryHoldings = $holdings->groupBy(function ($holding) {
            return $holding->asset->category ?: 'Other';
        });

        $categoryAllocation = $categoryHoldings->map(function ($group, $category) use ($holdingsValue) {
            $value = $group->sum(function ($holding) {
                return $holding->shares_owned *
                    ($holding->asset->share_price ?? 0);
            });

            return [
                'category' => $category,
                'value' => round($value, 2),
                'percentage' => $holdingsValue > 0
                    ? round(($value / $holdingsValue) * 100, 1)
                    : 0,
            ];
        })->sortByDesc('value')->values();

        // 4. Allocation by country
        $countryHoldings = $holdings->groupBy(function ($holding) {
            return $holding->asset->country ?: 'Unknown';
        });

        $geographicAllocation = $countryHoldings->map(function ($group, $country) use ($holdingsValue) {
            $value = $group->sum(function ($holding) {
                return $holding->shares_owned *
                    ($holding->asset->share_price ?? 0);
            });

            return [
                'country' => $country,
                'value' => round($value, 2),
                'percentage' => $holdingsValue > 0
                    ? round(($value / $holdingsValue) * 100, 1)
                    : 0,
            ];
        })->sortByDesc('value')->values();

        // 5. Dynamic risk calculations
        $totalHoldingsCount = $holdings->count();
        $uniqueCategories = $categoryHoldings->count();
        $uniqueCountries = $countryHoldings->count();

        $maxAssetShare = 0;

        if ($holdingsValue > 0) {
            $maxAssetValue = $holdings->max(function ($holding) {
                return $holding->shares_owned *
                    ($holding->asset->share_price ?? 0);
            });

            $maxAssetShare = ($maxAssetValue / $holdingsValue) * 100;
        }

        // 6. Diversification assessment
        if ($uniqueCategories >= 3 && $totalHoldingsCount >= 4) {
            $diversification = [
                'label' => 'Excellent',
                'class' => 'positive',
            ];
        } elseif ($uniqueCategories >= 2 || $totalHoldingsCount >= 2) {
            $diversification = [
                'label' => 'Good',
                'class' => 'positive',
            ];
        } else {
            $diversification = [
                'label' => 'Concentrated',
                'class' => 'negative',
            ];
        }

        // 7. Asset concentration assessment
        if ($maxAssetShare > 50) {
            $concentration = [
                'label' => 'High',
                'class' => 'negative',
            ];
        } elseif ($maxAssetShare > 25) {
            $concentration = [
                'label' => 'Moderate',
                'class' => 'neutral',
            ];
        } else {
            $concentration = [
                'label' => 'Low',
                'class' => 'positive',
            ];
        }

        // 8. Geographic exposure assessment
        if ($uniqueCountries >= 3) {
            $geographicRisk = [
                'label' => 'Well Distributed',
                'class' => 'positive',
            ];
        } elseif ($uniqueCountries == 2) {
            $geographicRisk = [
                'label' => 'Moderate',
                'class' => 'neutral',
            ];
        } else {
            $geographicRisk = [
                'label' => 'Single Region',
                'class' => 'negative',
            ];
        }

        // 9. Development exposure
        $weightedDevExposure = 0;

        if ($holdingsValue > 0) {
            $weightedDevExposure = $holdings->sum(function ($holding) {
                $assetValue = $holding->shares_owned *
                    ($holding->asset->share_price ?? 0);

                return $assetValue *
                    ($holding->asset->development_exposure ?? 0);
            }) / $holdingsValue;
        }

        if ($weightedDevExposure > 40) {
            $devExposure = [
                'label' => 'High (' . round($weightedDevExposure) . '%)',
                'class' => 'negative',
            ];
        } elseif ($weightedDevExposure > 15) {
            $devExposure = [
                'label' => 'Moderate (' . round($weightedDevExposure) . '%)',
                'class' => 'neutral',
            ];
        } else {
            $devExposure = [
                'label' => 'Low (' . round($weightedDevExposure) . '%)',
                'class' => 'positive',
            ];
        }

        // 10. Liquidity assessment
        $userAssetIds = $holdings->pluck('asset_id')->unique();

        $activeListingsCount = $userAssetIds->isNotEmpty()
            ? SellOrder::whereIn('asset_id', $userAssetIds)
                ->where('status', 'active')
                ->distinct()
                ->count('asset_id')
            : 0;

        $adminLiquidityScore = 0;

        if ($totalHoldingsCount > 0) {
            $liquidityPoints = $holdings->sum(function ($holding) {
                $marketLiq = strtolower(
                    $holding->asset->market_liquidity ?? 'moderate'
                );

                return match ($marketLiq) {
                    'high', 'easy' => 3,
                    'moderate' => 2,
                    'low', 'hard' => 1,
                    default => 2,
                };
            });

            $adminLiquidityScore = $liquidityPoints / $totalHoldingsCount;
        }

        $totalLiquidityScore = $adminLiquidityScore +
            ($activeListingsCount > 0 ? 0.5 : 0);

        if ($totalLiquidityScore >= 2.5) {
            $liquidity = [
                'label' => 'High',
                'class' => 'positive',
            ];
        } elseif ($totalLiquidityScore >= 1.8) {
            $liquidity = [
                'label' => 'Moderate',
                'class' => 'neutral',
            ];
        } else {
            $liquidity = [
                'label' => 'Low',
                'class' => 'negative',
            ];
        }

        // 11. Overall risk score
        $rawScore = 5.0 -
            ($uniqueCategories * 0.8) +
            ($maxAssetShare / 25);

        $riskScore = max(1.0, min(10.0, round($rawScore, 1)));

        $riskLabel = $riskScore >= 7.0
            ? 'High Risk'
            : ($riskScore >= 4.0 ? 'Moderate' : 'Conservative');

        $riskBadgeClass = $riskScore >= 7.0
            ? 'negative'
            : ($riskScore >= 4.0 ? 'neutral' : 'positive');

        // 12. Return portfolio data
        return response()->json([
            'portfolio' => [
                'portfolio_value' => round($portfolioValue, 2),
                'invested_total' => round($holdingsValue, 2),
                'available_cash' => round($availableCash, 2),
                'holdings_count' => $totalHoldingsCount,
            ],

            'holdings' => $holdings,

            'allocation' => [
                'categories' => $categoryAllocation,
                'countries' => $geographicAllocation,
            ],

            'risk' => [
                'score' => $riskScore,
                'label' => $riskLabel,
                'badge_class' => $riskBadgeClass,

                'diversification' => $diversification,
                'concentration' => $concentration,
                'geographic_exposure' => $geographicRisk,
                'development_exposure' => $devExposure,
                'liquidity' => $liquidity,

                'metrics' => [
                    'unique_categories' => $uniqueCategories,
                    'unique_countries' => $uniqueCountries,
                    'largest_asset_share' => round($maxAssetShare, 2),
                    'weighted_development_exposure' => round($weightedDevExposure, 2),
                    'active_secondary_listings' => $activeListingsCount,
                    'average_liquidity_score' => round($adminLiquidityScore, 2),
                    'total_liquidity_score' => round($totalLiquidityScore, 2),
                ],
            ],
        ]);
    }
}
