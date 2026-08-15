<?php

use App\Enums\CategoryType;
use App\Enums\RecurrenceFrequency;
use App\Models\Account;
use App\Models\Bank;
use App\Models\Category;
use App\Models\CreditCard;
use App\Models\RecurrencePlan;
use App\Models\Release;
use App\Models\User;

function makeRecurrencePlan(User $user, Account $account, Category $category, array $overrides = []): RecurrencePlan
{
    return RecurrencePlan::create(array_merge([
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $category->id,
        'title' => 'Mensalidade',
        'amount' => 100,
        'type' => 'expense',
        'payment_method' => 'pix',
        'frequency' => RecurrenceFrequency::MONTHLY->value,
        'start_date' => '2026-01-31',
        'end_date' => '2026-12-31',
        'next_generation' => '2026-02-28',
        'active' => true,
    ], $overrides));
}

it('gera lançamentos sem drift de fim de mês', function () {
    $user = User::factory()->create();
    $bank = Bank::factory()->create();
    $account = Account::factory()->for($user)->for($bank)->create();
    $category = Category::factory()->create(['type' => CategoryType::EXPENSE->value]);

    $plan = makeRecurrencePlan($user, $account, $category);

    $dates = [];
    for ($i = 0; $i < 4; $i++) {
        $release = $plan->fresh()->generateNextRelease();
        if ($release === null) {
            break;
        }
        $dates[] = $release->date->format('Y-m-d');
    }

    expect($dates)->toBe(['2026-02-28', '2026-03-31', '2026-04-30', '2026-05-31']);
});

it('cria lançamentos recorrentes gerados como pending', function () {
    $user = User::factory()->create();
    $bank = Bank::factory()->create();
    $account = Account::factory()->for($user)->for($bank)->create();
    $category = Category::factory()->create(['type' => CategoryType::EXPENSE->value]);

    $plan = makeRecurrencePlan($user, $account, $category);

    $release = $plan->generateNextRelease();

    expect($release)->not()->toBeNull();
    expect($release->status->value)->toBe('pending');
    expect($release->recurrence_id)->toBe($plan->id);
});

it('encerra o plano quando a próxima geração ultrapassa a data final', function () {
    $user = User::factory()->create();
    $bank = Bank::factory()->create();
    $account = Account::factory()->for($user)->for($bank)->create();
    $category = Category::factory()->create(['type' => CategoryType::EXPENSE->value]);

    $plan = makeRecurrencePlan($user, $account, $category, [
        'end_date' => '2026-03-30',
    ]);

    $plan->generateNextRelease();

    expect($plan->fresh()->active)->toBe(false);
    expect(Release::where('recurrence_id', $plan->id)->count())->toBe(1);
});
