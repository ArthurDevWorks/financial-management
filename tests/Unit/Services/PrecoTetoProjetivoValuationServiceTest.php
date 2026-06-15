<?php

use App\Services\PrecoTetoProjetivoValuationService;

it('calcula preço teto projetivo com lucro, ações, payout e yield desejado', function () {
    $service = new PrecoTetoProjetivoValuationService();

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
