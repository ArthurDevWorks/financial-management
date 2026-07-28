<?php

use Illuminate\Support\Facades\Schedule;

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
