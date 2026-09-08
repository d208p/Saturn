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
});

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
    Route::post('/assets', [AdminDashboardController::class, 'storeAsset'])->name('assets.store');
});