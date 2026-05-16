<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\QuoteController;

Route::get('/api/quotes/daily', [QuoteController::class, 'daily']);
Route::post('/api/quotes/store', [QuoteController::class, 'store']);