<?php

use Illuminate\Support\Facades\Route;

use App\Models\Quote;

Route::get('/', function () {
    $quotes = Quote::all();

    return view('welcome', compact('quotes'));
});

use App\Http\Controllers\QuoteController;

Route::get('/quotes', [QuoteController::class, 'index']);
Route::post('/quotes', [QuoteController::class, 'store']);