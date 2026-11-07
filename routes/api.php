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
use App\Http\Controllers\Api\ApiDashboardController;
use App\Http\Controllers\Api\ApiWithdrawalController;
use App\Http\Controllers\Api\ApiBuyoutController;

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
    Route::get('/dashboard', [ApiDashboardController::class, 'index']);
    Route::get('/portfolio', [ApiPortfolioController::class, 'index']);
    Route::get('/income', [ApiIncomeController::class, 'index']);
    Route::post('/buyouts/{proposal}/vote', [ApiBuyoutController::class, 'vote']);

    Route::get('/assets/{asset}', [ApiAssetController::class, 'show']);
    Route::post('/assets/{asset}/watchlist', [ApiAssetController::class, 'toggleWatch']);

    Route::get('/secondary/asset/{asset}', [ApiSecondaryMarketController::class, 'showAssetListings']);
    Route::post('/secondary/asset/{asset}/buy', [ApiSecondaryMarketController::class, 'buyFromMarket']);
    Route::get('/listings/create/{asset}', [ApiListingController::class, 'create']);
    Route::post('/listings/store/{asset}', [ApiListingController::class, 'store']);
    Route::post('/listings/{sellOrder}/cancel', [ApiListingController::class, 'cancel']);
    Route::get('/trade/{asset}', [ApiTradeController::class, 'show']);
    Route::post('/trade/{asset}', [ApiTradeController::class, 'store']);

    Route::prefix('withdrawal')->group(function () {
        Route::get('/', [ApiWithdrawalController::class, 'index']);
        Route::post('/', [ApiWithdrawalController::class, 'process']);
    });

    // Account Management Endpoints
    Route::prefix('account')->group(function () {

        // Account information
        Route::get('/', [ApiAccountController::class, 'index']);

        // Profile and security
        Route::put('/profile', [ApiAccountController::class, 'updateProfile']);
        Route::put('/password', [ApiAccountController::class, 'updatePassword']);

        // Bank accounts
        Route::post('/bank', [ApiAccountController::class, 'storeBank']);

        // Stripe recipient onboarding and payouts
        Route::post('/stripe/onboarding', [ApiAccountController::class, 'startStripeOnboarding']);
        Route::get('/stripe/status', [ApiAccountController::class, 'refreshStripePayoutStatus']);

        // Identity verification
        Route::post('/verify/identity', [ApiAccountController::class, 'startIdentityVerification']);

        // Two-factor authentication
        Route::post('/2fa/enable', [ApiAccountController::class, 'enableTwoFactor']);
        Route::post('/2fa/disable', [ApiAccountController::class, 'disableTwoFactor']);

        // Accreditation
        Route::post('/accreditation', [ApiAccountController::class, 'requestAccreditation']);

        // Device and session management
        Route::delete('/sessions/{id}', [ApiAccountController::class, 'logoutSession']);

    });

    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy']);
});