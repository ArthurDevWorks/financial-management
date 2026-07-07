<?php

use App\Enums\AccountType;
use App\Models\Account;
use App\Models\Bank;
use App\Models\User;

it('retorna a listagem de contas no endpoint de contas', function () {
    $user = User::factory()->create();
    $bank = Bank::factory()->create();
    Account::factory()->for($user)->for($bank)->create([
        'type' => AccountType::CHECKING->value,
        'total' => 250,
    ]);

    $this->actingAs($user);

    $response = $this->get(route('accounts.index'));

    $response->assertOk();
});
