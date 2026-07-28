<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\BankController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GordonController;
use App\Http\Controllers\PrecoTetoController;
use App\Http\Controllers\ReleaseController;
use App\Http\Controllers\ValuationController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

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
    Route::get('valuations', [ValuationController::class, 'index'])->name('valuations.index');
    Route::get('valuations/create', [ValuationController::class, 'create'])->name('valuations.create');
    Route::post('valuations', [ValuationController::class, 'store'])->name('valuations.store');
    Route::get('valuations/{valuation}', [ValuationController::class, 'show'])->name('valuations.show');
    Route::put('valuations/{valuation}', [ValuationController::class, 'update'])->name('valuations.update');

    Route::get('preco-teto', [PrecoTetoController::class, 'index'])->name('preco-teto.index');
    Route::post('preco-teto', [PrecoTetoController::class, 'store'])->name('preco-teto.store');
    Route::put('preco-teto/{valuation}', [PrecoTetoController::class, 'update'])->name('preco-teto.update');

    Route::get('gordon', [GordonController::class, 'index'])->name('gordon.index');
    Route::post('gordon', [GordonController::class, 'store'])->name('gordon.store');
    Route::put('gordon/{valuation}', [GordonController::class, 'update'])->name('gordon.update');
})->middleware(['auth', 'verified']);

Route::prefix('screening')->name('screening.')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [\App\Http\Controllers\ScreeningController::class, 'index'])->name('index');
    Route::get('/json', [\App\Http\Controllers\ScreeningController::class, 'json'])->name('json');
    Route::get('/compare', [\App\Http\Controllers\ScreeningController::class, 'compare'])->name('compare');
    Route::get('/{ticker}', [\App\Http\Controllers\ScreeningController::class, 'show'])->name('show');
    Route::get('/{ticker}/valuation', [\App\Http\Controllers\ScreeningController::class, 'valuation'])->name('valuation');
    Route::post('/favorite', [\App\Http\Controllers\ScreeningController::class, 'toggleFavorite'])->name('favorite');
    Route::post('/filters', [\App\Http\Controllers\ScreeningController::class, 'saveFilter'])->name('filters.save');
    Route::delete('/filters/{filter}', [\App\Http\Controllers\ScreeningController::class, 'deleteFilter'])->name('filters.delete');
});

require __DIR__.'/settings.php';
