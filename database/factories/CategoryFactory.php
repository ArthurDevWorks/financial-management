<?php

namespace Database\Factories;

use App\Enums\CategoryType;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'type' => CategoryType::REVENUE->value,
            'name' => fake()->unique()->word(),
        ];
    }

    public function revenue(): static
    {
        return $this->state(fn () => ['type' => CategoryType::REVENUE->value]);
    }

    public function expense(): static
    {
        return $this->state(fn () => ['type' => CategoryType::EXPENSE->value]);
    }

    public function investment(): static
    {
        return $this->state(fn () => ['type' => CategoryType::INVESTMENT->value]);
    }
}
