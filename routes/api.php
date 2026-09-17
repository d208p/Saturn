<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Api\ApiMarketController;
use App\Http\Controllers\Api\ApiPortfolioController;
use App\Http\Controllers\Api\ApiAccountController;
use App\Http\Controllers\Api\ApiIncomeController;
use App\Http\Controllers\Api\ApiAssetController;
use App\Http\Controllers\Api\ApiSecondaryMarketController;
use App\Http\Controllers\Api\ApiListingController;
use App\Http\Controllers\Api\ApiTradeController;

// Public authentication routes for Flutter
Route::post('/login', [AuthenticatedSessionController::class, 'store']);
Route::post('/register', [RegisteredUserController::class, 'store']);

// Protected routes requiring a Sanctum token
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Mobile API Endpoints
    Route::get('/market', [ApiMarketController::class, 'index']);
    Route::get('/portfolio', [ApiPortfolioController::class, 'index']);
    Route::get('/income', [ApiIncomeController::class, 'index']);
    Route::get('/asset/{asset}', [ApiAssetController::class, 'show']);
    Route::get('/secondary/asset/{asset}', [ApiSecondaryMarketController::class, 'showAssetListings']);
    Route::post('/secondary/asset/{asset}/buy', [ApiSecondaryMarketController::class, 'buyFromMarket']);
    Route::get('/listings/create/{asset}', [ApiListingController::class, 'create']);
    Route::post('/listings/store/{asset}', [ApiListingController::class, 'store']);
    Route::post('/listings/{sellOrder}/cancel', [ApiListingController::class, 'cancel']);
    Route::get('/trade/{asset}', [ApiTradeController::class, 'show']);
    Route::post('/trade/{asset}', [ApiTradeController::class, 'store']);

    // 2. Added Account Management Endpoints
    Route::prefix('account')->group(function () {
        Route::get('/', [ApiAccountController::class, 'index']);
        Route::put('/profile', [ApiAccountController::class, 'updateProfile']);
        Route::put('/password', [ApiAccountController::class, 'updatePassword']);
        Route::post('/bank', [ApiAccountController::class, 'storeBank']);
        Route::post('/withdraw', [ApiAccountController::class, 'withdraw']);
        Route::post('/accreditation', [ApiAccountController::class, 'requestAccreditation']);
        Route::delete('/sessions/{id}', [ApiAccountController::class, 'logoutSession']);
    });

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
});