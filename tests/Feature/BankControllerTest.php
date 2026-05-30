<?php

use App\Models\Bank;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

it('lista bancos com contagem de contas e url do logo', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $bank = Bank::factory()->create(['logo' => 'banks/logo-teste.png']);

    $response = $this->get(route('banks.index'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('banks/Index')
        ->has('banks.data', 1)
        ->where('banks.data.0.id', $bank->id)
        ->where('banks.data.0.logo_url', asset('storage/banks/logo-teste.png'))
    );
});

it('cadastra banco com upload de logo', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $arquivo = UploadedFile::fake()->create('logo.png', 10, 'image/png');

    $response = $this->post(route('banks.store'), [
        'name' => 'Banco Teste',
        'logo' => $arquivo,
    ]);

    $response->assertRedirect(route('banks.index'));
    $response->assertSessionHas('sucess', 'Banco cadastrado com sucesso');

    $bank = Bank::query()->latest('id')->first();

    expect($bank)->not()->toBeNull();
    expect($bank->name)->toBe('Banco Teste');
    Storage::disk('public')->assertExists($bank->logo);
});

it('atualiza banco substituindo o logo anterior', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $bank = Bank::factory()->create([
        'name' => 'Banco Original',
        'logo' => 'banks/logo-antigo.png',
    ]);

    Storage::disk('public')->put('banks/logo-antigo.png', 'conteudo-antigo');

    $novoLogo = UploadedFile::fake()->create('novo-logo.png', 10, 'image/png');

    $response = $this->put(route('banks.update', $bank), [
        'name' => 'Banco Atualizado',
        'logo' => $novoLogo,
    ]);

    $response->assertRedirect(route('banks.index'));
    $response->assertSessionHas('success', 'Banco atualizado com sucesso');

    $bank->refresh();

    expect($bank->name)->toBe('Banco Atualizado');
    Storage::disk('public')->assertMissing('banks/logo-antigo.png');
    Storage::disk('public')->assertExists($bank->logo);
});

it('remove o banco e apaga o arquivo do logo', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $bank = Bank::factory()->create([
        'logo' => 'banks/logo-remover.png',
    ]);

    Storage::disk('public')->put('banks/logo-remover.png', 'conteudo');

    $response = $this->delete(route('banks.destroy', $bank));

    $response->assertRedirect(route('banks.index'));
    Storage::disk('public')->assertMissing('banks/logo-remover.png');
    $this->assertSoftDeleted('banks', ['id' => $bank->id]);
});
