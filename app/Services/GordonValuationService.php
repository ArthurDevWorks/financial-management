<?php

namespace App\Services;

class GordonValuationService
{
    public function calculate(array $data): array
    {
        $dps = (float) $data['dps'];
        $discountRate = ((float) $data['discount_rate']) / 100;
        $growthPerpetuity = ((float) $data['growth_perpetuity']) / 100;
        $currentPrice = isset($data['current_price']) && $data['current_price'] !== null
            ? (float) $data['current_price']
            : null;
        $projectionYears = (int) ($data['projection_years'] ?? 5);
        $growthRates = array_map(
            static fn (mixed $value): float => max(0.0, ((float) $value) / 100),
            $data['growth_rates'] ?? [],
        );

        if (count($growthRates) < $projectionYears) {
            $lastRate = !empty($growthRates) ? end($growthRates) : $growthPerpetuity;
            $growthRates = array_pad($growthRates, $projectionYears, $lastRate);
        }
        $growthRates = array_slice($growthRates, 0, $projectionYears);

        // Fair price via Gordon: P = DPS / (Ke - g)
        $fairPrice = $discountRate > $growthPerpetuity && $dps > 0
            ? $dps / ($discountRate - $growthPerpetuity)
            : null;

        // Projected dividends
        $projectedDividends = [];
        $currentDps = $dps;

        for ($year = 1; $year <= $projectionYears; $year++) {
            $yearGrowth = $growthRates[$year - 1];
            $nextDps = $currentDps * (1 + $yearGrowth);
            $discountFactor = pow(1 + $discountRate, $year);
            $presentValue = $nextDps / $discountFactor;

            $projectedDividends[] = [
                'year' => $year,
                'growth_rate' => round($yearGrowth * 100, 2),
                'dps' => round($nextDps, 2),
                'discount_factor' => round($discountFactor, 4),
                'present_value' => round($presentValue, 2),
            ];

            $currentDps = $nextDps;
        }

        // Terminal value (perpetuity after projection years)
        $terminalGrowthRate = $growthPerpetuity;
        $terminalDps = $currentDps * (1 + $terminalGrowthRate);
        $terminalValue = $discountRate > $terminalGrowthRate
            ? $terminalDps / ($discountRate - $terminalGrowthRate)
            : 0.0;
        $terminalPresentValue = $terminalValue / pow(1 + $discountRate, $projectionYears);

        $pvOfDividends = array_sum(array_column($projectedDividends, 'present_value'));
        $totalEquityValue = $pvOfDividends + $terminalPresentValue;

        // Upside / downside
        $upside = null;
        $marginOfSafety = null;

        if ($fairPrice !== null && $currentPrice !== null && $currentPrice > 0) {
            $upside = (($fairPrice / $currentPrice) - 1) * 100;
            $marginOfSafety = (1 - ($currentPrice / $fairPrice)) * 100;
        }

        return [
            'assumptions' => [
                'dps' => round($dps, 4),
                'discount_rate' => round($discountRate * 100, 2),
                'growth_perpetuity' => round($growthPerpetuity * 100, 2),
                'current_price' => $currentPrice !== null ? round($currentPrice, 2) : null,
                'projection_years' => $projectionYears,
                'growth_rates' => array_map(
                    static fn (float $rate): float => round($rate * 100, 2),
                    $growthRates,
                ),
            ],
            'projected_cash_flows' => $projectedDividends,
            'summary' => [
                'fair_price' => $fairPrice !== null ? round($fairPrice, 2) : null,
                'pv_of_dividends' => round($pvOfDividends, 2),
                'terminal_value' => round($terminalValue, 2),
                'terminal_present_value' => round($terminalPresentValue, 2),
                'total_equity_value' => round($totalEquityValue, 2),
                'upside' => $upside !== null ? round($upside, 2) : null,
                'margin_of_safety' => $marginOfSafety !== null ? round($marginOfSafety, 2) : null,
            ],
        ];
    }
}
