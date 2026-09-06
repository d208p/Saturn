<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;

// Public login route for API / Flutter
Route::post('/login', [AuthenticatedSessionController::class, 'store']);

// Protected routes requiring a Sanctum token
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
});

// Public registration route for API / Flutter
Route::post('/register', [RegisteredUserController::class, 'store']);