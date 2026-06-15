<?php

namespace App\Services;

class PrecoTetoProjetivoValuationService
{
    public function calculate(array $data): array
    {
        $desiredYieldRate = ((float) $data['desired_yield']) / 100;
        $projectedPayoutRate = ((float) $data['projected_payout']) / 100;
        $projectedNetIncome = (float) $data['projected_net_income'];
        $totalShares = (int) $data['total_shares'];
        $projectedGrowthRate = ((float) $data['projected_growth_rate']) / 100;
        $currentPricePerShare = (float) $data['current_price_per_share'];

        $projectedNetIncomeAfterGrowth = $projectedNetIncome * (1 + $projectedGrowthRate);
        $projectedEps = $projectedNetIncomeAfterGrowth / $totalShares;
        $projectedDps = $projectedEps * $projectedPayoutRate;
        $priceCeiling = $projectedDps / $desiredYieldRate;
        $projectedYield = ($projectedDps / $currentPricePerShare) * 100;
        $marginOfSafety = (($priceCeiling - $currentPricePerShare) / $priceCeiling) * 100;
        $upside = (($priceCeiling / $currentPricePerShare) - 1) * 100;

        return [
            'assumptions' => [
                'desired_yield' => round($desiredYieldRate * 100, 2),
                'projected_payout' => round($projectedPayoutRate * 100, 2),
                'projected_net_income' => round($projectedNetIncome, 2),
                'total_shares' => $totalShares,
                'projected_growth_rate' => round($projectedGrowthRate * 100, 2),
                'current_price_per_share' => round($currentPricePerShare, 2),
            ],
            'projected_cash_flows' => [],
            'summary' => [
                'projected_net_income_after_growth' => round($projectedNetIncomeAfterGrowth, 2),
                'projected_eps' => round($projectedEps, 4),
                'projected_dps' => round($projectedDps, 4),
                'price_ceiling' => round($priceCeiling, 2),
                'fair_value_per_share' => round($priceCeiling, 2),
                'current_price_per_share' => round($currentPricePerShare, 2),
                'projected_yield' => round($projectedYield, 2),
                'margin_of_safety' => round($marginOfSafety, 2),
                'upside' => round($upside, 2),
            ],
        ];
    }
}
