<?php

use App\Enums\CategoryType;
use App\Models\Account;
use App\Models\Bank;
use App\Models\Category;
use App\Models\CreditCard;
use App\Models\Release;
use App\Models\User;

it('lista lançamentos apenas do usuário autenticado', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $bank = Bank::factory()->create();
    $account = Account::factory()->for($user)->for($bank)->create();
    $category = Category::factory()->create(['type' => CategoryType::REVENUE->value]);

    Release::factory()->for($user)->for($account)->for($category, 'category')->create([
        'title' => 'Lançamento do usuário',
        'date' => now()->subDay()->toDateString(),
    ]);

    Release::factory()->for($otherUser)->create([
        'title' => 'Lançamento de outro usuário',
    ]);

    $this->actingAs($user);

    $response = $this->get(route('releases.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('releases/Index')
        ->has('releases.data', 1)
        ->where('releases.data.0.title', 'Lançamento do usuário')
    );
});

it('permite criar lançamento para o usuário autenticado', function () {
    $user = User::factory()->create();
    $bank = Bank::factory()->create();
    $account = Account::factory()->for($user)->for($bank)->create();
    $category = Category::factory()->create(['type' => CategoryType::REVENUE->value]);

    $this->actingAs($user);

    $response = $this->post(route('releases.store'), [
        'account_id' => $account->id,
        'category_id' => $category->id,
        'title' => 'Receita extra',
        'description' => 'Descrição opcional',
        'amount' => '99.90',
        'type' => 'revenue',
        'date' => now()->toDateString(),
    ]);

    $response->assertRedirect(route('releases.index'));
    $response->assertSessionHas('success', 'Lançamento criado com sucesso.');

    $this->assertDatabaseHas('releases', [
        'user_id' => $user->id,
        'account_id' => $account->id,
        'category_id' => $category->id,
        'title' => 'Receita extra',
        'amount' => 99.90,
        'type' => 'revenue',
    ]);
});

it('bloqueia edição atualização e exclusão de lançamento de outro usuário', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();
    $bank = Bank::factory()->create();
    $account = Account::factory()->for($otherUser)->for($bank)->create();
    $category = Category::factory()->create(['type' => CategoryType::EXPENSE->value]);
    $release = Release::factory()->for($otherUser)->for($account)->for($category, 'category')->expense()->create();

    $this->actingAs($user);

    $this->get(route('releases.edit', $release))->assertForbidden();
    $this->put(route('releases.update', $release), [
        'account_id' => $account->id,
        'category_id' => $category->id,
        'title' => 'Tentativa',
        'description' => null,
        'amount' => '10.00',
        'type' => 'expense',
        'date' => now()->toDateString(),
    ])->assertForbidden();

    $this->delete(route('releases.destroy', $release))->assertForbidden();
});

it('rejeita parcelamento acima de 255 parcelas', function () {
    $user = User::factory()->create();
    $bank = Bank::factory()->create();
    $account = Account::factory()->for($user)->for($bank)->create();
    $category = Category::factory()->create(['type' => CategoryType::EXPENSE->value]);
    $creditCard = CreditCard::create([
        'user_id' => $user->id,
        'bank_id' => $bank->id,
        'name' => 'Visa',
        'limit' => 5000,
        'closing_day' => 10,
        'due_day' => 15,
    ]);

    $this->actingAs($user);

    $this->post(route('releases.store'), [
        'account_id' => $account->id,
        'category_id' => $category->id,
        'title' => 'Compra parcelada',
        'amount' => '1000.00',
        'type' => 'expense',
        'date' => now()->toDateString(),
        'payment_method' => 'credit_card',
        'credit_card_id' => $creditCard->id,
        'is_installment' => true,
        'total_installments' => 256,
    ])->assertSessionHasErrors('total_installments');

    $this->assertDatabaseMissing('releases', ['title' => 'Compra parcelada']);
});

it('rejeita parcelamento que gera parcelas de valor zero', function () {
    $user = User::factory()->create();
    $bank = Bank::factory()->create();
    $account = Account::factory()->for($user)->for($bank)->create();
    $category = Category::factory()->create(['type' => CategoryType::EXPENSE->value]);
    $creditCard = CreditCard::create([
        'user_id' => $user->id,
        'bank_id' => $bank->id,
        'name' => 'Visa',
        'limit' => 5000,
        'closing_day' => 10,
        'due_day' => 15,
    ]);

    $this->actingAs($user);

    $this->post(route('releases.store'), [
        'account_id' => $account->id,
        'category_id' => $category->id,
        'title' => 'Compra de 1 real em muitas parcelas',
        'amount' => '1.00',
        'type' => 'expense',
        'date' => now()->toDateString(),
        'payment_method' => 'credit_card',
        'credit_card_id' => $creditCard->id,
        'is_installment' => true,
        'total_installments' => 200,
    ])->assertSessionHasErrors('total_installments');

    $this->assertDatabaseMissing('releases', ['title' => 'Compra de 1 real em muitas parcelas']);
});

it('rejeita lançamento parcelado e recorrente ao mesmo tempo', function () {
    $user = User::factory()->create();
    $bank = Bank::factory()->create();
    $account = Account::factory()->for($user)->for($bank)->create();
    $category = Category::factory()->create(['type' => CategoryType::EXPENSE->value]);
    $creditCard = CreditCard::create([
        'user_id' => $user->id,
        'bank_id' => $bank->id,
        'name' => 'Visa',
        'limit' => 5000,
        'closing_day' => 10,
        'due_day' => 15,
    ]);

    $this->actingAs($user);

    $this->post(route('releases.store'), [
        'account_id' => $account->id,
        'category_id' => $category->id,
        'title' => 'Conflitante',
        'amount' => '100.00',
        'type' => 'expense',
        'date' => now()->toDateString(),
        'payment_method' => 'credit_card',
        'credit_card_id' => $creditCard->id,
        'is_installment' => true,
        'total_installments' => 3,
        'is_recurring' => true,
        'recurrence_frequency' => 'monthly',
        'recurrence_end_date' => now()->addMonths(6)->toDateString(),
    ])->assertSessionHasErrors('is_recurring');

    $this->assertDatabaseMissing('releases', ['title' => 'Conflitante']);
});

it('cria parcelas com status pending para parcelas futuras', function () {
    $user = User::factory()->create();
    $bank = Bank::factory()->create();
    $account = Account::factory()->for($user)->for($bank)->create();
    $category = Category::factory()->create(['type' => CategoryType::EXPENSE->value]);
    $creditCard = CreditCard::create([
        'user_id' => $user->id,
        'bank_id' => $bank->id,
        'name' => 'Visa',
        'limit' => 5000,
        'closing_day' => 10,
        'due_day' => 15,
    ]);

    $this->actingAs($user);

    $this->post(route('releases.store'), [
        'account_id' => $account->id,
        'category_id' => $category->id,
        'title' => 'Parcelado em 3x',
        'amount' => '100.00',
        'type' => 'expense',
        'date' => now()->startOfMonth()->toDateString(),
        'payment_method' => 'credit_card',
        'credit_card_id' => $creditCard->id,
        'is_installment' => true,
        'total_installments' => 3,
    ])->assertSessionHas('success');

    $releases = Release::where('title', 'Parcelado em 3x')->orderBy('installment_number')->get();

    expect($releases)->toHaveCount(3);
    expect($releases[0]->status->value)->toBe('paid');
    expect($releases[1]->status->value)->toBe('pending');
    expect($releases[2]->status->value)->toBe('pending');
    expect((float) $releases[0]->amount)->toBe(33.33);
    expect((float) $releases[1]->amount)->toBe(33.33);
    expect((float) $releases[2]->amount)->toBe(33.34);
});
