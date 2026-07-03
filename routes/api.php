<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuoteController;

Route::get('quote', QuoteController::class);
