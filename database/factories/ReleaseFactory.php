<?php

namespace Database\Factories;

use App\Models\Account;
use App\Models\Category;
use App\Models\Release;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Release>
 */
class ReleaseFactory extends Factory
{
    protected $model = Release::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'account_id' => Account::factory(),
            'category_id' => Category::factory(),
            'title' => fake()->sentence(3),
            'description' => fake()->optional()->sentence(),
            'amount' => fake()->randomFloat(2, 1, 5000),
            'type' => 'revenue',
            'date' => now()->toDateString(),
        ];
    }

    public function revenue(): static
    {
        return $this->state(fn () => ['type' => 'revenue']);
    }

    public function expense(): static
    {
        return $this->state(fn () => ['type' => 'expense']);
    }
}
