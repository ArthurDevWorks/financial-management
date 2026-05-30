<?php

namespace Database\Factories;

use App\Enums\AccountType;
use App\Models\Account;
use App\Models\Bank;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Account>
 */
class AccountFactory extends Factory
{
    protected $model = Account::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'bank_id' => Bank::factory(),
            'type' => AccountType::CHECKING->value,
            'agency' => fake()->numerify('####'),
            'account' => fake()->numerify('#########'),
            'total' => fake()->randomFloat(2, 0, 10000),
        ];
    }

    public function type(AccountType|string $type): static
    {
        return $this->state(fn () => [
            'type' => $type instanceof AccountType ? $type->value : $type,
        ]);
    }
}
