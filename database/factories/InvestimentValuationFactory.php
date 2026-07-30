<?php

namespace Database\Factories;

use App\Models\Asset;
use App\Models\InvestimentValuation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvestimentValuation>
 */
class InvestimentValuationFactory extends Factory
{
    protected $model = InvestimentValuation::class;

    public function definition(): array
    {
        return [
            'asset_id' => Asset::factory(),
            'method' => InvestimentValuation::METHOD_DCF,
            'assumptions' => [
                'asset_id' => 1,
                'current_fcf' => 100,
                'discount_rate' => 12,
                'terminal_growth_rate' => 3,
                'projection_years' => 5,
                'total_shares' => 10,
                'payout' => 75,
                'roe' => 24,
                'current_price_per_share' => 100,
                'growth_rates' => [6, 6, 6, 6, 6],
            ],
            'calculated_at' => now(),
        ];
    }
}
