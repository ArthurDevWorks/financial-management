<?php

use App\Enums\CategoryType;
use App\Models\Account;
use App\Models\Bank;
use App\Models\Category;
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
