<?php

use App\Enums\AccountType;
use App\Enums\CategoryType;
use App\Models\Account;
use App\Models\Bank;
use App\Models\Category;
use App\Models\Release;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

it('mostra o dashboard com totais e transações do período atual', function () {
    $user = User::factory()->create();
    $bank = Bank::factory()->create();
    $account = Account::factory()->for($user)->for($bank)->type(AccountType::CHECKING)->create([
        'total' => 1000,
    ]);
    $revenueCategory = Category::factory()->create(['type' => CategoryType::REVENUE->value, 'name' => 'Salário']);
    $expenseCategory = Category::factory()->create(['type' => CategoryType::EXPENSE->value, 'name' => 'Alimentação']);

    Release::factory()->for($user)->for($account)->for($revenueCategory, 'category')->revenue()->create([
        'title' => 'Receita 1',
        'amount' => 500,
        'date' => now()->toDateString(),
    ]);
    Release::factory()->for($user)->for($account)->for($expenseCategory, 'category')->expense()->create([
        'title' => 'Despesa 1',
        'amount' => 150,
        'date' => now()->toDateString(),
    ]);

    $this->actingAs($user);

    $response = $this->get(route('dashboard', [
        'period' => 'month',
        'month' => now()->month,
        'year' => now()->year,
    ]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->where('summary.totalRevenue', 500)
        ->where('summary.totalExpense', 150)
        ->where('summary.netBalance', 350)
        ->where('summary.totalInitialBalance', 1000)
        ->where('summary.totalBalance', 1350)
        ->has('recentTransactions', 2)
        ->has('revenuesByCategory', 1)
        ->has('expensesByCategory', 1)
        ->has('accountsEvolution', 1)
    );
});

it('aceita filtro customizado por intervalo de datas', function () {
    $user = User::factory()->create();
    $bank = Bank::factory()->create();
    $account = Account::factory()->for($user)->for($bank)->create(['total' => 100]);
    $category = Category::factory()->create(['type' => CategoryType::REVENUE->value]);

    Release::factory()->for($user)->for($account)->for($category, 'category')->create([
        'title' => 'Fora do intervalo',
        'amount' => 300,
        'date' => now()->subDays(10)->toDateString(),
    ]);
    Release::factory()->for($user)->for($account)->for($category, 'category')->create([
        'title' => 'Dentro do intervalo',
        'amount' => 200,
        'date' => now()->subDay()->toDateString(),
    ]);

    $this->actingAs($user);

    $response = $this->get(route('dashboard', [
        'period' => 'custom',
        'start_date' => now()->subDays(3)->toDateString(),
        'end_date' => now()->toDateString(),
    ]));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->where('summary.totalRevenue', 200)
        ->where('summary.totalExpense', 0)
        ->has('recentTransactions', 1)
    );
});

