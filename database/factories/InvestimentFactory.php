<?php

namespace Database\Factories;

use App\Enums\FixedIncomeIndexer;
use App\Enums\FixedIncomeProfitabilityType;
use App\Enums\InvestmentAssetType;
use App\Models\Investiment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Investiment>
 */
class InvestimentFactory extends Factory
{
    protected $model = Investiment::class;

    public function definition(): array
    {
        $assetType = fake()->randomElement(InvestmentAssetType::cases());
        $quantity = fake()->randomFloat(4, 1, 200);
        $averagePrice = fake()->randomFloat(2, 10, 500);
        $investedAmount = $quantity * $averagePrice;
        $currentBalance = round($investedAmount * fake()->randomFloat(4, 0.75, 1.35), 2);
        $profitability = $investedAmount > 0
            ? (($currentBalance - $investedAmount) / $investedAmount) * 100
            : 0;

        return [
            'name' => strtoupper(fake()->lexify('????')).fake()->numberBetween(1, 11),
            'dt_investment' => now()->toDateTimeString(),
            'type' => $assetType->value,
            'quantity' => $quantity,
            'average_price' => $averagePrice,
            'current_balance' => $currentBalance,
            'value' => $averagePrice,
            'profitability' => round($profitability, 2),
            'profitability_type' => $assetType->isFixedIncome()
                ? fake()->randomElement(FixedIncomeProfitabilityType::cases())->value
                : null,
            'indexer' => $assetType->isFixedIncome()
                ? fake()->randomElement(FixedIncomeIndexer::cases())->value
                : null,
            'contracted_rate' => $assetType->isFixedIncome()
                ? fake()->randomFloat(2, 6, 120)
                : null,
            'maturity_date' => $assetType->isFixedIncome()
                ? now()->addYears(fake()->numberBetween(1, 8))->toDateString()
                : null,
            'liquidity' => $assetType->isFixedIncome()
                ? fake()->randomElement(['D+0', 'D+1', 'No vencimento'])
                : null,
        ];
    }

    public function stock(): self
    {
        return $this->state(fn (): array => [
            'type' => InvestmentAssetType::STOCK->value,
            'profitability_type' => null,
            'indexer' => null,
            'contracted_rate' => null,
            'maturity_date' => null,
            'liquidity' => null,
        ]);
    }

    public function cdb(): self
    {
        return $this->state(fn (): array => [
            'type' => InvestmentAssetType::CDB->value,
            'profitability_type' => FixedIncomeProfitabilityType::POST_FIXED->value,
            'indexer' => FixedIncomeIndexer::CDI->value,
            'contracted_rate' => 110,
            'maturity_date' => now()->addYears(2)->toDateString(),
            'liquidity' => 'D+1',
        ]);
    }
}
