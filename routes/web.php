<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\PreregisterController;
use App\Http\Controllers\TradeController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\IncomeController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\ActivityController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\SecondaryMarketController;
use App\Http\Controllers\ListingController;
use App\Http\Controllers\DepositController;
use App\Http\Controllers\WithdrawalController;
use App\Http\Controllers\StripeWebHookController;

Route::get('/', function () {
    return view('welcome');
});

// Preregistration routes
Route::get('/preregister', [PreregisterController::class, 'create'])->name('preregister');
Route::post('/preregister', [PreregisterController::class, 'store'])->name('preregister.store');

// Authentication routes
Route::get('/login', function () {
    return view('auth.login');
})->name('login');

Route::post('/login', [AuthenticatedSessionController::class, 'store']);

Route::get('/register', function () {
    return view('auth.register');
})->name('register');

Route::post('/register', [RegisteredUserController::class, 'store']);

Route::post(
    '/stripe/webhook',
    [StripeWebhookController::class, 'handle']
)->name('stripe.webhook');

Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// Authenticated group
Route::middleware('auth')->group(function () {
    // Dynamic Portfolio Route
    Route::get('/portfolio', function () {
        $user = Auth::user()->load('holdings.asset');
        return view('dashboard.portfolio', compact('user'));
    })->name('portfolio');

    // Static View Routes
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/market', [MarketController::class, 'index'])->name('market');
    Route::get('/asset/{asset}', [AssetController::class, 'show'])->name('asset.show');
    Route::get('/income', [IncomeController::class, 'index'])->name('income');
    Route::get('/activity', [ActivityController::class, 'index'])->name('activity');
    Route::get('/trade/{asset}', [TradeController::class, 'show'])->name('trade.show');
    Route::post('/trade/{asset}', [TradeController::class, 'store'])->name('trade.store');
    Route::get('/account', [AccountController::class, 'index'])->name('account.index');
    Route::put('/account/profile', [AccountController::class, 'updateProfile'])->name('account.profile');
    Route::put('/account/password', [AccountController::class, 'updatePassword'])->name('account.password');
    Route::get(
        '/account/stripe/onboarding',
        [AccountController::class, 'startStripeOnboarding']
    )->name('account.stripe.onboarding');

    Route::get(
        '/account/stripe/return',
        [AccountController::class, 'stripeOnboardingReturn']
    )->name('account.stripe.return');

    Route::get(
        '/account/stripe/refresh',
        [AccountController::class, 'stripeOnboardingRefresh']
    )->name('account.stripe.refresh');
    Route::post('/account/accreditation', [AccountController::class, 'requestAccreditation'])->name('account.accreditation');
    Route::delete('/account/session/{id}', [AccountController::class, 'logoutSession'])->name('account.session.destroy');
    Route::get('/secondary/asset/{asset}', [SecondaryMarketController::class, 'showAssetListings'])->name('secondary.asset.show');
    Route::post('/secondary/asset/{asset}/buy', [SecondaryMarketController::class, 'buyFromMarket'])->name('secondary.asset.buy');
    Route::get('/listings/create/{asset}', [ListingController::class, 'create'])->name('listings.create');
    Route::post('/listings/store/{asset}', [ListingController::class, 'store'])->name('listings.store');
    Route::post('/listings/{sellOrder}/cancel', [ListingController::class, 'cancel'])->name('listings.cancel');
    Route::get('/deposit', [DepositController::class, 'show'])
        ->name('deposit.show');

    Route::post('/deposit/create-payment-intent', [DepositController::class, 'createPaymentIntent'])
        ->name('deposit.createPaymentIntent');

    // withdraw cash
    Route::get('/withdraw', [WithdrawalController::class, 'show'])->name('withdraw.show');
    Route::post('/withdraw', [WithdrawalController::class, 'process'])->name('withdraw.process');
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::post('/assets', [AdminDashboardController::class, 'storeAsset'])->name('assets.store');
    Route::post('/assets/distribute', [AdminDashboardController::class, 'distributeProfit'])->name('assets.distribute');
});