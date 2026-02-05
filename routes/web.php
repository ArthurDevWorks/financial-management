<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\InvestimentController;
use App\Http\Controllers\RevenueController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::resource('banks', BankController::class);
})->middleware(['auth', 'verified']);

Route::middleware(['auth'])->group(function () {
    Route::resource('accounts', AccountController::class);
})->middleware(['auth', 'verified']);

Route::middleware(['auth'])->group(function () {
    Route::resource('revenues', RevenueController::class);
})->middleware(['auth', 'verified']);

Route::middleware(['auth'])->group(function () {
    Route::resource('expenses', ExpenseController::class);
})->middleware(['auth', 'verified']);

Route::middleware(['auth'])->group(function () {
    Route::resource('categories', CategoryController::class);
})->middleware(['auth', 'verified']);

Route::middleware(['auth'])->group(function () {
    Route::resource('investiments', InvestimentController::class);
})->middleware(['auth', 'verified']);

require __DIR__.'/settings.php';
