<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ExerciseController;

//Test route
Route::get('/test', function () {
    return response()->json(["message" => "API working"]);
});

//Log in and registration routes
Route::post("/register", [AuthController::class, "register"]);
Route::post("/login", [AuthController::class, "login"]);

// Protected routes
Route::middleware("auth:sanctum")->group(function () {
    Route::post("/logout", [AuthController::class, "logout"]);

    Route::get("/profile", function () {
        return response()->json(auth()->user());
    });
});

// Exercise search and store routes
Route::get("/exercises/search", [ExerciseController::class, "search"]);