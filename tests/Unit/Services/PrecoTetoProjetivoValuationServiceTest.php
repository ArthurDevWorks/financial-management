<?php

use App\Services\PrecoTetoProjetivoValuationService;

it('calcula preço teto projetivo com lucro, ações, payout e yield desejado', function () {
    $service = new PrecoTetoProjetivoValuationService;

    $resultado = $service->calculate([
        'desired_yield' => 6,
        'projected_payout' => 55,
        'projected_net_income' => 3450000000,
        'total_shares' => 640321918,
        'projected_growth_rate' => 0,
        'current_price_per_share' => 49.51,
    ]);

    expect($resultado['summary']['projected_eps'])->toBe(5.3879);
    expect($resultado['summary']['projected_dps'])->toBe(2.9634);
    expect($resultado['summary']['price_ceiling'])->toBe(49.39);
    expect($resultado['summary']['fair_value_per_share'])->toBe(49.39);
    expect($resultado['summary']['projected_yield'])->toBe(5.99);
    expect($resultado['summary']['margin_of_safety'])->toBe(-0.24);
});

it('usa o valor justo como base para a margem de segurança', function () {
    $service = new PrecoTetoProjetivoValuationService;

    $resultado = $service->calculate([
        'desired_yield' => 10,
        'projected_payout' => 50,
        'projected_net_income' => 1000000,
        'total_shares' => 1000000,
        'projected_growth_rate' => 0,
        'current_price_per_share' => 2.5,
    ]);

    expect($resultado['summary']['price_ceiling'])->toBe(5.0);
    expect($resultado['summary']['margin_of_safety'])->toBe(50.0);
    expect($resultado['summary']['upside'])->toBe(100.0);
});

it('não divide por zero quando recebe denominadores zerados', function () {
    $service = new PrecoTetoProjetivoValuationService;

    $resultado = $service->calculate([
        'desired_yield' => 0,
        'projected_payout' => 55,
        'projected_net_income' => 3450000000,
        'total_shares' => 0,
        'projected_growth_rate' => 0,
        'current_price_per_share' => 0,
    ]);

    expect($resultado['summary']['projected_eps'])->toBe(0.0);
    expect($resultado['summary']['price_ceiling'])->toBe(0.0);
    expect($resultado['summary']['projected_yield'])->toBe(0.0);
    expect($resultado['summary']['margin_of_safety'])->toBe(0.0);
    expect($resultado['summary']['upside'])->toBe(0.0);
});
