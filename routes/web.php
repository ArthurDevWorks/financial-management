<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\InvestimentController;
use App\Http\Controllers\ReleaseController;
use App\Http\Controllers\PrecoTetoController;
use App\Http\Controllers\ValuationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

use App\Http\Controllers\DashboardController;

Route::get('dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware(['auth'])->group(function () {
    Route::get('banks/export', [BankController::class, 'export'])->name('banks.export');
    Route::resource('banks', BankController::class);
})->middleware(['auth', 'verified']);

Route::middleware(['auth'])->group(function () {
    Route::get('accounts/export', [AccountController::class, 'export'])->name('accounts.export');
    Route::resource('accounts', AccountController::class);
})->middleware(['auth', 'verified']);

Route::middleware(['auth'])->group(function () {
    Route::get('releases/export', [ReleaseController::class, 'export'])->name('releases.export');
    Route::resource('releases', ReleaseController::class);
})->middleware(['auth', 'verified']);

Route::middleware(['auth'])->group(function () {
    Route::get('categories/export', [CategoryController::class, 'export'])->name('categories.export');
    Route::resource('categories', CategoryController::class);
})->middleware(['auth', 'verified']);

Route::middleware(['auth'])->group(function () {
    Route::resource('investiments', InvestimentController::class);
})->middleware(['auth', 'verified']);

Route::middleware(['auth'])->group(function () {
    Route::get('valuations', [ValuationController::class, 'index'])->name('valuations.index');
    Route::get('valuations/create', [ValuationController::class, 'create'])->name('valuations.create');
    Route::get('preco-teto', [PrecoTetoController::class, 'index'])->name('preco-teto.index');
})->middleware(['auth', 'verified']);

require __DIR__.'/settings.php';
