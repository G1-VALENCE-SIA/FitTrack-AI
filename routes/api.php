<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ExerciseController;
use App\Http\Controllers\NutritionController;
use App\Http\Controllers\BmiController;
use App\Http\Controllers\WeatherController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\RecommendationController;
use App\Http\Controllers\WorkoutController;

//Test route
Route::get('/test', fn() => response()->json(['message' => 'API working']));

//Log in and registration routes
Route::post("/register", [AuthController::class, "register"]);
Route::post("/login", [AuthController::class, "login"]);

//Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::put('/profile', [AuthController::class, 'updateProfile']);

    // Exercise
    Route::get('/exercises/search', [ExerciseController::class, 'search']);
    Route::get('/exercises/{id}', [ExerciseController::class, 'show']);

    // Workouts & logging
    Route::post('/workouts', [WorkoutController::class, 'store']);
    Route::get('/workouts/{id}', [WorkoutController::class, 'show']);
    Route::post('/workouts/{id}/log', [WorkoutController::class, 'logSession']);
    Route::get('/logs', [WorkoutController::class, 'logs']);

    // Nutrition
    Route::get('/nutrition/search', [NutritionController::class, 'search']);
    Route::get('/nutrition/suggest', [NutritionController::class, 'suggest']);

    // BMI
    Route::post('/bmi/calculate', [BmiController::class, 'calculate']);
    Route::get('/bmi/history', [BmiController::class, 'history']);

    // Weather
    Route::get('/weather/{city}', [WeatherController::class, 'getWeather']);

    // Quotes
    Route::get('/quotes/daily', [QuoteController::class, 'daily']);

    // Recommendations
    Route::post('/recommendations/generate', [RecommendationController::class, 'generate']);
});
