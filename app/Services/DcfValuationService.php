<?php

namespace App\Services;

class DcfValuationService
{
    public function calculate(array $data): array
    {
        $currentFcf = (float) $data['current_fcf'];
        $payoutRate = ((float) $data['payout']) / 100;
        $roeRate = ((float) $data['roe']) / 100;
        $discountRate = ((float) $data['discount_rate']) / 100;
        $terminalGrowthRate = ((float) $data['terminal_growth_rate']) / 100;
        $projectionYears = (int) $data['projection_years'];
        $totalShares = (float) $data['total_shares'];
        $currentPricePerShare = isset($data['current_price_per_share']) && $data['current_price_per_share'] !== null
            ? (float) $data['current_price_per_share']
            : null;
        $baseGrowthRate = $roeRate * (1 - $payoutRate);

        $growthRates = array_map(
            static fn (mixed $value): float => max(0.0, ((float) $value) / 100),
            $data['growth_rates'] ?? [],
        );

        if (count($growthRates) < $projectionYears) {
            $growthRates = array_pad($growthRates, $projectionYears, $baseGrowthRate);
        }

        $growthRates = array_slice($growthRates, 0, $projectionYears);

        $projectedCashFlows = [];
        $presentValueOfCashFlows = 0.0;

        $projectedFcf = $currentFcf;

        for ($year = 1; $year <= $projectionYears; $year++) {
            $yearGrowthRate = $growthRates[$year - 1];
            $projectedFcf *= (1 + $yearGrowthRate);
            $discountFactor = pow(1 + $discountRate, $year);
            $presentValue = $projectedFcf / $discountFactor;

            $projectedCashFlows[] = [
                'year' => $year,
                'growth_rate' => round($yearGrowthRate * 100, 2),
                'projected_fcf' => round($projectedFcf, 2),
                'discount_factor' => round($discountFactor, 4),
                'present_value' => round($presentValue, 2),
            ];

            $presentValueOfCashFlows += $presentValue;
        }

        $terminalCashFlow = $projectedFcf * (1 + $terminalGrowthRate);
        $terminalSpread = $discountRate - $terminalGrowthRate;
        $terminalValue = $terminalSpread > 0
            ? $terminalCashFlow / $terminalSpread
            : 0.0;
        $terminalPresentValue = $terminalValue / pow(1 + $discountRate, $projectionYears);

        $equityValue = $presentValueOfCashFlows + $terminalPresentValue;
        $fairValuePerShare = $totalShares > 0
            ? $equityValue / $totalShares
            : 0.0;
        $marketCap = $currentPricePerShare !== null ? $currentPricePerShare * $totalShares : null;

        $upside = null;
        $marginOfSafety = null;

        if ($currentPricePerShare !== null && $currentPricePerShare > 0) {
            $upside = (($fairValuePerShare / $currentPricePerShare) - 1) * 100;
        }

        if ($currentPricePerShare !== null && $fairValuePerShare > 0) {
            $marginOfSafety = (1 - ($currentPricePerShare / $fairValuePerShare)) * 100;
        }

        return [
            'assumptions' => [
                'current_fcf' => round($currentFcf, 2),
                'payout' => round($payoutRate * 100, 2),
                'roe' => round($roeRate * 100, 2),
                'base_growth_rate' => round($baseGrowthRate * 100, 2),
                'discount_rate' => round($discountRate * 100, 2),
                'terminal_growth_rate' => round($terminalGrowthRate * 100, 2),
                'projection_years' => $projectionYears,
                'total_shares' => round($totalShares, 2),
                'current_price_per_share' => $currentPricePerShare !== null ? round($currentPricePerShare, 2) : null,
                'growth_rates' => array_map(
                    static fn (float $rate): float => round($rate * 100, 2),
                    $growthRates,
                ),
            ],
            'projected_cash_flows' => $projectedCashFlows,
            'summary' => [
                'present_value_of_cash_flows' => round($presentValueOfCashFlows, 2),
                'terminal_value' => round($terminalValue, 2),
                'terminal_present_value' => round($terminalPresentValue, 2),
                'equity_value' => round($equityValue, 2),
                'fair_value_per_share' => round($fairValuePerShare, 2),
                'market_cap' => $marketCap !== null ? round($marketCap, 2) : null,
                'upside' => $upside !== null ? round($upside, 2) : null,
                'margin_of_safety' => $marginOfSafety !== null ? round($marginOfSafety, 2) : null,
            ],
        ];
    }
}
