<?php

namespace Database\Factories;

use App\Enums\CategoryType;
use App\Models\Category;
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
        return [
            'name' => fake()->company(),
            'dt_investment' => now()->toDateTimeString(),
            'value' => fake()->randomFloat(2, 10, 10000),
            'type' => Category::factory()->create([
                'type' => CategoryType::INVESTMENT->value,
            ])->id,
            'profitability' => fake()->numberBetween(-50, 50),
        ];
    }
}
