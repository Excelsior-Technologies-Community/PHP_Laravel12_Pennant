<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeatureController;
use App\Http\Controllers\FeatureAuditController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::view('/dashboard', 'dashboard')
        ->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Existing New Dashboard Feature
    |--------------------------------------------------------------------------
    */

    Route::post('/feature/on', [DashboardController::class, 'enableFeature'])
        ->name('feature.on');

    Route::post('/feature/off', [DashboardController::class, 'disableFeature'])
        ->name('feature.off');

    /*
    |--------------------------------------------------------------------------
    | Feature Management
    |--------------------------------------------------------------------------
    */

    Route::get('/features', [FeatureController::class, 'index'])
        ->name('features.index');

    Route::post('/features/enable', [FeatureController::class, 'enable'])
        ->name('features.enable');

    Route::post('/features/disable', [FeatureController::class, 'disable'])
        ->name('features.disable');

    Route::get('/features/audit', [FeatureAuditController::class, 'index'])
    ->name('features.audit');
});

Route::middleware('auth')->group(function () {

    Route::get('/profile', function () {
        return view('profile.edit');
    })->name('profile.edit');

});

require __DIR__.'/auth.php';