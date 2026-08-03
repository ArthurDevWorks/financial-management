<?php

use App\Enums\AccountType;
use App\Enums\CategoryType;
use App\Models\Account;
use App\Models\Bank;
use App\Models\Category;
use App\Models\Release;
use App\Models\User;

it('permite criar conta e associa o usuário autenticado', function () {
    $user = User::factory()->create();
    $bank = Bank::factory()->create();

    $this->actingAs($user);

    $response = $this->post(route('accounts.store'), [
        'bank_id' => $bank->id,
        'type' => AccountType::CHECKING->value,
        'agency' => '1234',
        'account' => '987654',
        'total' => '1500.50',
    ]);

    $response->assertRedirect(route('accounts.index'));
    $response->assertSessionHas('success', 'Conta cadastrada com sucesso');

    $this->assertDatabaseHas('accounts', [
        'user_id' => $user->id,
        'bank_id' => $bank->id,
        'type' => AccountType::CHECKING->value,
        'agency' => '1234',
        'account' => '987654',
        'total' => 1500.50,
    ]);
});

it('lista contas com saldo corrente calculado', function () {
    $user = User::factory()->create();
    $bank = Bank::factory()->create(['logo' => 'banks/logo-teste.png']);
    $account = Account::factory()->for($user)->for($bank)->create(['total' => 1000]);
    $revenueCategory = Category::factory()->create(['type' => CategoryType::REVENUE->value]);
    $expenseCategory = Category::factory()->create(['type' => CategoryType::EXPENSE->value]);

    Release::factory()->for($user)->for($account)->for($revenueCategory, 'category')->revenue()->create([
        'amount' => 250,
        'date' => now()->toDateString(),
    ]);
    Release::factory()->for($user)->for($account)->for($expenseCategory, 'category')->expense()->create([
        'amount' => 100,
        'date' => now()->toDateString(),
    ]);

    $this->actingAs($user);

    $response = $this->get(route('accounts.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('accounts/Index')
        ->has('accounts.data', 1)
        ->where('accounts.data.0.id', $account->id)
        ->where('accounts.data.0.revenue_sum', '250.00')
        ->where('accounts.data.0.expense_sum', '100.00')
        ->where('accounts.data.0.bank.logo_url', asset('storage/banks/logo-teste.png'))
    );
});

it('atualiza e remove conta', function () {
    $user = User::factory()->create();
    $bank = Bank::factory()->create();
    $account = Account::factory()->for($user)->for($bank)->create();

    $this->actingAs($user);

    $response = $this->put(route('accounts.update', $account), [
        'bank_id' => $bank->id,
        'type' => AccountType::SAVINGS->value,
        'agency' => '4321',
        'account' => '111111',
        'total' => '2000.00',
    ]);

    $response->assertRedirect(route('accounts.index'));
    $response->assertSessionHas('success', 'Conta atualizada com sucesso');

    $this->delete(route('accounts.destroy', $account))
        ->assertRedirect(route('accounts.index'));

    $this->assertSoftDeleted('accounts', ['id' => $account->id]);
});
