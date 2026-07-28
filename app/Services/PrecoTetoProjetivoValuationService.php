<?php

namespace App\Services;

class PrecoTetoProjetivoValuationService
{
    public function calculate(array $data): array
    {
        $desiredYieldRate = ((float) $data['desired_yield']) / 100;
        $projectedPayoutRate = ((float) $data['projected_payout']) / 100;
        $projectedNetIncome = (float) $data['projected_net_income'];
        $totalShares = (float) $data['total_shares'];
        $projectedGrowthRate = ((float) $data['projected_growth_rate']) / 100;
        $currentPricePerShare = (float) $data['current_price_per_share'];

        $projectedNetIncomeAfterGrowth = $projectedNetIncome * (1 + $projectedGrowthRate);
        $projectedEps = $totalShares > 0
            ? $projectedNetIncomeAfterGrowth / $totalShares
            : 0.0;
        $projectedDps = $projectedEps * $projectedPayoutRate;
        $priceCeiling = $desiredYieldRate > 0
            ? $projectedDps / $desiredYieldRate
            : 0.0;
        $projectedYield = $currentPricePerShare > 0
            ? ($projectedDps / $currentPricePerShare) * 100
            : 0.0;
        // Margem de segurança = diferença entre preço teto e cotação atual,
        // expressa como percentual da cotação (quanto o ativo está "barato" hoje)
        $marginOfSafety = $currentPricePerShare > 0
            ? (($priceCeiling - $currentPricePerShare) / $currentPricePerShare) * 100
            : 0.0;
        $upside = $currentPricePerShare > 0
            ? (($priceCeiling / $currentPricePerShare) - 1) * 100
            : 0.0;

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
