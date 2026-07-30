<?php

namespace Database\Factories;

use App\Models\Asset;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Asset>
 */
class AssetFactory extends Factory
{
    protected $model = Asset::class;

    public function definition(): array
    {
        return [
            'ticker' => strtoupper(fake()->lexify('????')).fake()->numberBetween(3, 11),
            'name' => fake()->company(),
            'asset_type' => 'stock',
            'current_price' => fake()->randomFloat(2, 10, 500),
            'logo_url' => null,
            'fetched_at' => now()->subHours(fake()->numberBetween(1, 48)),
        ];
    }

    public function stock(): self
    {
        return $this->state([
            'asset_type' => 'stock',
        ]);
    }

    public function fii(): self
    {
        return $this->state([
            'asset_type' => 'fii',
        ]);
    }
}
