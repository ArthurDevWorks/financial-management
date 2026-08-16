<?php

use App\Enums\RecurrenceFrequency;

it('preserva o fim de mês em recorrências mensais', function () {
    $date = Carbon\Carbon::parse('2026-01-31');

    $next = RecurrenceFrequency::MONTHLY->addToDate($date->copy());

    expect($next->format('Y-m-d'))->toBe('2026-02-28');
});

it('preserva o fim de mês em recorrências trimestrais', function () {
    $date = Carbon\Carbon::parse('2026-11-30');

    $next = RecurrenceFrequency::QUARTERLY->addToDate($date->copy());

    expect($next->format('Y-m-d'))->toBe('2027-02-28');
});

it('preserva o fim de mês em recorrências anuais', function () {
    $date = Carbon\Carbon::parse('2028-02-29');

    $next = RecurrenceFrequency::YEARLY->addToDate($date->copy());

    expect($next->format('Y-m-d'))->toBe('2029-02-28');
});

it('adiciona dias em recorrências semanais e quinzenais', function () {
    $date = Carbon\Carbon::parse('2026-01-31');

    expect(RecurrenceFrequency::WEEKLY->addToDate($date->copy())->format('Y-m-d'))->toBe('2026-02-07');
    expect(RecurrenceFrequency::BIWEEKLY->addToDate($date->copy())->format('Y-m-d'))->toBe('2026-02-14');
});
