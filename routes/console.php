<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('releases:generate-recurring')->daily()->withoutOverlapping();

Schedule::command('assets:sync', ['--hours' => 4])
    ->weekdays()
    ->at('10:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/assets-sync.log'));

Schedule::command('assets:sync', ['--hours' => 4])
    ->weekdays()
    ->at('13:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/assets-sync.log'));

Schedule::command('assets:sync', ['--hours' => 4])
    ->weekdays()
    ->at('18:00')
    ->withoutOverlapping()
    ->appendOutputTo(storage_path('logs/assets-sync.log'));
