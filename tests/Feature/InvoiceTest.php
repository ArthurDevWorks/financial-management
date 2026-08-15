<?php

use App\Enums\CategoryType;
use App\Models\Account;
use App\Models\Bank;
use App\Models\Category;
use App\Models\CreditCard;
use App\Models\Release;
use App\Models\User;

it('exclui lançamentos cancelados do total e do período da fatura', function () {
    $user = User::factory()->create();
    $bank = Bank::factory()->create();
    $card = CreditCard::create([
        'user_id' => $user->id,
        'bank_id' => $bank->id,
        'name' => 'Visa',
        'limit' => 5000,
        'closing_day' => 10,
        'due_day' => 15,
    ]);

    $category = Category::factory()->create(['type' => CategoryType::EXPENSE->value]);

    Release::factory()->for($user)->for($card)->for($category, 'category')->expense()->create([
        'amount' => 100,
        'date' => now()->toDateString(),
        'status' => 'paid',
        'payment_method' => 'credit_card',
    ]);
    Release::factory()->for($user)->for($card)->for($category, 'category')->expense()->create([
        'amount' => 50,
        'date' => now()->toDateString(),
        'status' => 'canceled',
        'payment_method' => 'credit_card',
    ]);

    $this->actingAs($user);

    $period = $card->invoicePeriod(now()->month, now()->year);

    expect($card->invoiceTotal(now()->month, now()->year))->toBe(100.0);
    expect($card->invoiceReleases(now()->month, now()->year)->count())->toBe(1);
    expect($period['start'])->toBe(Carbon\Carbon::now()->subMonth()->day(11)->toDateString());
    expect($period['end'])->toBe(Carbon\Carbon::now()->day(10)->toDateString());
});

it('subtrai reembolsos do total da fatura', function () {
    $user = User::factory()->create();
    $bank = Bank::factory()->create();
    $card = CreditCard::create([
        'user_id' => $user->id,
        'bank_id' => $bank->id,
        'name' => 'Visa',
        'limit' => 5000,
        'closing_day' => 10,
        'due_day' => 15,
    ]);

    $expenseCategory = Category::factory()->create(['type' => CategoryType::EXPENSE->value]);
    $revenueCategory = Category::factory()->create(['type' => CategoryType::REVENUE->value]);

    Release::factory()->for($user)->for($card)->for($expenseCategory, 'category')->expense()->create([
        'amount' => 100,
        'date' => now()->toDateString(),
        'status' => 'paid',
        'payment_method' => 'credit_card',
    ]);
    Release::factory()->for($user)->for($card)->for($revenueCategory, 'category')->revenue()->create([
        'amount' => 30,
        'date' => now()->toDateString(),
        'status' => 'paid',
        'payment_method' => 'credit_card',
    ]);

    $this->actingAs($user);

    expect($card->invoiceTotal(now()->month, now()->year))->toBe(70.0);
});

it('não considera lançamento em dinheiro na fatura do cartão', function () {
    $user = User::factory()->create();
    $bank = Bank::factory()->create();
    $card = CreditCard::create([
        'user_id' => $user->id,
        'bank_id' => $bank->id,
        'name' => 'Visa',
        'limit' => 5000,
        'closing_day' => 10,
        'due_day' => 15,
    ]);

    $category = Category::factory()->create(['type' => CategoryType::EXPENSE->value]);

    Release::factory()->for($user)->for($card)->for($category, 'category')->expense()->create([
        'amount' => 100,
        'date' => now()->toDateString(),
        'status' => 'paid',
        'payment_method' => 'cash',
    ]);

    $this->actingAs($user);

    expect($card->invoiceTotal(now()->month, now()->year))->toBe(0.0);
});

it('projeta vencimento no mês seguinte quando o vencimento é antes do fechamento', function () {
    $user = User::factory()->create();
    $bank = Bank::factory()->create();
    $card = CreditCard::create([
        'user_id' => $user->id,
        'bank_id' => $bank->id,
        'name' => 'Visa',
        'limit' => 5000,
        'closing_day' => 28,
        'due_day' => 5,
    ]);

    $period = $card->invoicePeriod(7, 2026);

    expect($period['start'])->toBe('2026-06-29');
    expect($period['end'])->toBe('2026-07-28');
    expect($period['due'])->toBe('2026-08-05');
});

it('mantém vencimento no mesmo mês quando vence após o fechamento', function () {
    $user = User::factory()->create();
    $bank = Bank::factory()->create();
    $card = CreditCard::create([
        'user_id' => $user->id,
        'bank_id' => $bank->id,
        'name' => 'Visa',
        'limit' => 5000,
        'closing_day' => 5,
        'due_day' => 15,
    ]);

    $period = $card->invoicePeriod(7, 2026);

    expect($period['start'])->toBe('2026-06-06');
    expect($period['end'])->toBe('2026-07-05');
    expect($period['due'])->toBe('2026-07-15');
});
