<?php

use App\Services\FiiSegmentMapper;

test('normalize maps brapi tijolo segment', function () {
    $result = FiiSegmentMapper::normalize('tijolo', 'Logística', 'fii');

    expect($result['sector'])->toBe('FII');
    expect($result['subsector'])->toBe('Logística');
    expect($result['segment'])->toBe('Tijolo');
});

test('normalize maps brapi papel segment', function () {
    $result = FiiSegmentMapper::normalize('papel', null, 'fii');

    expect($result['sector'])->toBe('FII');
    expect($result['subsector'])->toBeNull();
    expect($result['segment'])->toBe('Papel');
});

test('normalize maps brapi hibrido segment', function () {
    $result = FiiSegmentMapper::normalize('híbrido', null, 'fii');

    expect($result['sector'])->toBe('FII');
    expect($result['subsector'])->toBeNull();
    expect($result['segment'])->toBe('Híbrido');
});

test('normalize maps brapi fof segment', function () {
    $result = FiiSegmentMapper::normalize('fof', null, 'fii');

    expect($result['sector'])->toBe('FII');
    expect($result['subsector'])->toBeNull();
    expect($result['segment'])->toBe('Fundo de Fundos');
});

test('normalize maps brapi tijolo with logistica subsegment', function () {
    $result = FiiSegmentMapper::normalize('tijolo', 'Logística', 'fii');

    expect($result['sector'])->toBe('FII');
    expect($result['subsector'])->toBe('Logística');
    expect($result['segment'])->toBe('Tijolo');
});

test('normalize maps brapi tijolo with shoppings subsegment', function () {
    $result = FiiSegmentMapper::normalize('tijolo', 'Shoppings', 'fii');

    expect($result['sector'])->toBe('FII');
    expect($result['subsector'])->toBe('Shoppings');
    expect($result['segment'])->toBe('Tijolo');
});

test('normalize maps brapi tijolo with lajes corporativas subsegment', function () {
    $result = FiiSegmentMapper::normalize('tijolo', 'Lajes Corporativas', 'fii');

    expect($result['sector'])->toBe('FII');
    expect($result['subsector'])->toBe('Lajes Corporativas');
    expect($result['segment'])->toBe('Tijolo');
});

test('normalize maps brapi tijolo with residencial subsegment', function () {
    $result = FiiSegmentMapper::normalize('tijolo', 'Residencial', 'fii');

    expect($result['sector'])->toBe('FII');
    expect($result['subsector'])->toBe('Residencial');
    expect($result['segment'])->toBe('Tijolo');
});

test('normalize maps brapi tijolo with varejo subsegment', function () {
    $result = FiiSegmentMapper::normalize('tijolo', 'Varejo', 'fii');

    expect($result['sector'])->toBe('FII');
    expect($result['subsector'])->toBe('Varejo');
    expect($result['segment'])->toBe('Tijolo');
});

test('normalize maps brapi tijolo with multicategoria subsegment', function () {
    $result = FiiSegmentMapper::normalize('tijolo', 'Multicategoria', 'fii');

    expect($result['sector'])->toBe('FII');
    expect($result['subsector'])->toBe('Multicategoria');
    expect($result['segment'])->toBe('Tijolo');
});

test('normalize maps status invest logistica to tijolo', function () {
    $result = FiiSegmentMapper::normalize('Logística', null, 'fii');

    expect($result['sector'])->toBe('FII');
    expect($result['subsector'])->toBeNull();
    expect($result['segment'])->toBe('Tijolo');
});

test('normalize maps status invest shoppings to tijolo', function () {
    $result = FiiSegmentMapper::normalize('Shoppings', null, 'fii');

    expect($result['sector'])->toBe('FII');
    expect($result['subsector'])->toBeNull();
    expect($result['segment'])->toBe('Tijolo');
});

test('normalize maps status invest lajes corporativas to tijolo', function () {
    $result = FiiSegmentMapper::normalize('Lajes Corporativas', null, 'fii');

    expect($result['sector'])->toBe('FII');
    expect($result['subsector'])->toBeNull();
    expect($result['segment'])->toBe('Tijolo');
});

test('normalize maps status invest residencial to tijolo', function () {
    $result = FiiSegmentMapper::normalize('Residencial', null, 'fii');

    expect($result['sector'])->toBe('FII');
    expect($result['subsector'])->toBeNull();
    expect($result['segment'])->toBe('Tijolo');
});

test('normalize maps status invest varejo to tijolo', function () {
    $result = FiiSegmentMapper::normalize('Varejo', null, 'fii');

    expect($result['sector'])->toBe('FII');
    expect($result['subsector'])->toBeNull();
    expect($result['segment'])->toBe('Tijolo');
});

test('normalize maps status invest educacional to tijolo', function () {
    $result = FiiSegmentMapper::normalize('Educacional', null, 'fii');

    expect($result['sector'])->toBe('FII');
    expect($result['subsector'])->toBeNull();
    expect($result['segment'])->toBe('Tijolo');
});

test('normalize maps status invest saude to tijolo', function () {
    $result = FiiSegmentMapper::normalize('Saúde', null, 'fii');

    expect($result['sector'])->toBe('FII');
    expect($result['subsector'])->toBeNull();
    expect($result['segment'])->toBe('Tijolo');
});

test('normalize maps status invest hotel to tijolo', function () {
    $result = FiiSegmentMapper::normalize('Hotel', null, 'fii');

    expect($result['sector'])->toBe('FII');
    expect($result['subsector'])->toBeNull();
    expect($result['segment'])->toBe('Tijolo');
});

test('normalize maps status invest hospitalar to tijolo', function () {
    $result = FiiSegmentMapper::normalize('Hospitalar', null, 'fii');

    expect($result['sector'])->toBe('FII');
    expect($result['subsector'])->toBeNull();
    expect($result['segment'])->toBe('Tijolo');
});

test('normalize maps status invest corporativo to tijolo', function () {
    $result = FiiSegmentMapper::normalize('Corporativo', null, 'fii');

    expect($result['sector'])->toBe('FII');
    expect($result['subsector'])->toBeNull();
    expect($result['segment'])->toBe('Tijolo');
});

test('normalize maps status invest industrial to tijolo', function () {
    $result = FiiSegmentMapper::normalize('Industrial', null, 'fii');

    expect($result['sector'])->toBe('FII');
    expect($result['subsector'])->toBeNull();
    expect($result['segment'])->toBe('Tijolo');
});

test('normalize maps status invest galpao to tijolo', function () {
    $result = FiiSegmentMapper::normalize('Galpão', null, 'fii');

    expect($result['sector'])->toBe('FII');
    expect($result['subsector'])->toBeNull();
    expect($result['segment'])->toBe('Tijolo');
});

test('normalize maps status invest imobiliario to tijolo', function () {
    $result = FiiSegmentMapper::normalize('Imobiliário', null, 'fii');

    expect($result['sector'])->toBe('FII');
    expect($result['subsector'])->toBeNull();
    expect($result['segment'])->toBe('Tijolo');
});

test('normalize maps status invest multimercado to hedge', function () {
    $result = FiiSegmentMapper::normalize('Multimercado', null, 'fii');

    expect($result['sector'])->toBe('FII');
    expect($result['subsector'])->toBeNull();
    expect($result['segment'])->toBe('Hedge');
});

test('normalize maps status invest papel', function () {
    $result = FiiSegmentMapper::normalize('Papel', null, 'fii');

    expect($result['sector'])->toBe('FII');
    expect($result['subsector'])->toBeNull();
    expect($result['segment'])->toBe('Papel');
});

test('normalize maps status invest fundo de fundos', function () {
    $result = FiiSegmentMapper::normalize('Fundo de Fundos', null, 'fii');

    expect($result['sector'])->toBe('FII');
    expect($result['subsector'])->toBeNull();
    expect($result['segment'])->toBe('Fundo de Fundos');
});

test('normalize maps status invest hibrido', function () {
    $result = FiiSegmentMapper::normalize('Híbrido', null, 'fii');

    expect($result['sector'])->toBe('FII');
    expect($result['subsector'])->toBeNull();
    expect($result['segment'])->toBe('Híbrido');
});

test('normalize maps status invest hedge', function () {
    $result = FiiSegmentMapper::normalize('Hedge', null, 'fii');

    expect($result['sector'])->toBe('FII');
    expect($result['subsector'])->toBeNull();
    expect($result['segment'])->toBe('Hedge');
});

test('normalize ignores non-fii asset type', function () {
    $result = FiiSegmentMapper::normalize('tijolo', 'Logística', 'stock');

    expect($result['sector'])->toBeNull();
    expect($result['subsector'])->toBeNull();
    expect($result['segment'])->toBeNull();
});

test('normalize handles null asset type', function () {
    $result = FiiSegmentMapper::normalize('tijolo', 'Logística', null);

    expect($result['sector'])->toBe('FII');
    expect($result['subsector'])->toBe('Logística');
    expect($result['segment'])->toBe('Tijolo');
});

test('normalize handles null segment type', function () {
    $result = FiiSegmentMapper::normalize(null, null, 'fii');

    expect($result['sector'])->toBe('FII');
    expect($result['subsector'])->toBeNull();
    expect($result['segment'])->toBeNull();
});

test('normalize handles unknown segment type', function () {
    $result = FiiSegmentMapper::normalize('unknown', null, 'fii');

    expect($result['sector'])->toBe('FII');
    expect($result['subsector'])->toBeNull();
    expect($result['segment'])->toBe('unknown');
});

test('normalize handles empty subsegment', function () {
    $result = FiiSegmentMapper::normalize('tijolo', '', 'fii');

    expect($result['sector'])->toBe('FII');
    expect($result['subsector'])->toBeNull();
    expect($result['segment'])->toBe('Tijolo');
});

test('normalize handles mixed case segment type', function () {
    $result = FiiSegmentMapper::normalize('TIJOLO', 'Logística', 'fii');

    expect($result['sector'])->toBe('FII');
    expect($result['subsector'])->toBe('Logística');
    expect($result['segment'])->toBe('Tijolo');
});

test('knownSegments returns unique values', function () {
    $segments = FiiSegmentMapper::knownSegments();

    expect($segments)->toBeArray();
    expect(count($segments))->toBeGreaterThan(3);
    expect($segments)->toContain('Tijolo');
    expect($segments)->toContain('Papel');
    expect($segments)->toContain('Híbrido');
    expect($segments)->toContain('Fundo de Fundos');
});

test('knownSubsegments returns unique values', function () {
    $subsegments = FiiSegmentMapper::knownSubsegments();

    expect($subsegments)->toBeArray();
    expect(count($subsegments))->toBeGreaterThan(5);
    expect($subsegments)->toContain('Logística');
    expect($subsegments)->toContain('Shoppings');
    expect($subsegments)->toContain('Lajes Corporativas');
});
