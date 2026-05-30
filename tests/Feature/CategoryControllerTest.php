<?php

use App\Enums\CategoryType;
use App\Models\Category;
use App\Models\User;

it('lista categorias ordenadas por tipo e nome', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    Category::factory()->create(['type' => CategoryType::EXPENSE->value, 'name' => 'Zeta']);
    Category::factory()->create(['type' => CategoryType::REVENUE->value, 'name' => 'Alpha']);
    Category::factory()->create(['type' => CategoryType::REVENUE->value, 'name' => 'Beta']);

    $response = $this->get(route('categories.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('categories/Index')
        ->has('categories.data', 3)
        ->where('categories.data.0.type', CategoryType::EXPENSE->value)
        ->where('categories.data.1.type', CategoryType::REVENUE->value)
        ->where('categories.data.1.name', 'Alpha')
        ->where('categories.data.2.name', 'Beta')
    );
});

it('cadastra atualiza e remove categoria', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $storeResponse = $this->post(route('categories.store'), [
        'type' => CategoryType::REVENUE->value,
        'name' => 'Salário',
    ]);

    $storeResponse->assertRedirect(route('categories.index'));
    $storeResponse->assertSessionHas('success', 'Categoria cadastrada com sucesso');

    $category = Category::query()->latest('id')->first();
    expect($category)->not()->toBeNull();

    $updateResponse = $this->put(route('categories.update', $category), [
        'type' => CategoryType::EXPENSE->value,
        'name' => 'Alimentação',
    ]);

    $updateResponse->assertRedirect(route('categories.index'));
    $updateResponse->assertSessionHas('success', 'Categoria atualizada com sucesso');

    $deleteResponse = $this->delete(route('categories.destroy', $category));

    $deleteResponse->assertRedirect(route('categories.index'));
    $this->assertSoftDeleted('categories', ['id' => $category->id]);
});
