<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FeatureController;
use App\Http\Controllers\FeatureAuditController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware([
    'auth',
    'verified',
])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | Dashboard
    |--------------------------------------------------------------------------
    */

    Route::view(
        '/dashboard',
        'dashboard'
    )->name('dashboard');

    /*
    |--------------------------------------------------------------------------
    | Existing New Dashboard Feature
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/feature/on',
        [
            DashboardController::class,
            'enableFeature',
        ]
    )->name('feature.on');

    Route::post(
        '/feature/off',
        [
            DashboardController::class,
            'disableFeature',
        ]
    )->name('feature.off');

    /*
    |--------------------------------------------------------------------------
    | Feature Management
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/features',
        [
            FeatureController::class,
            'index',
        ]
    )->name('features.index');

    Route::post(
        '/features/enable',
        [
            FeatureController::class,
            'enable',
        ]
    )->name('features.enable');

    Route::post(
        '/features/disable',
        [
            FeatureController::class,
            'disable',
        ]
    )->name('features.disable');

    /*
    |--------------------------------------------------------------------------
    | New Bulk Features
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/features/bulk-enable',
        [
            FeatureController::class,
            'bulkEnable',
        ]
    )->name('features.bulk-enable');

    Route::post(
        '/features/bulk-disable',
        [
            FeatureController::class,
            'bulkDisable',
        ]
    )->name('features.bulk-disable');

    /*
    |--------------------------------------------------------------------------
    | Reset User Override
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/features/reset-override',
        [
            FeatureController::class,
            'resetOverride',
        ]
    )->name('features.reset-override');

    /*
    |--------------------------------------------------------------------------
    | CSV Export
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/features/export',
        [
            FeatureController::class,
            'export',
        ]
    )->name('features.export');

    /*
    |--------------------------------------------------------------------------
    | Audit History
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/features/audit',
        [
            FeatureAuditController::class,
            'index',
        ]
    )->name('features.audit');
});

Route::middleware('auth')->group(function () {

    Route::get(
        '/profile',
        function () {
            return view('profile.edit');
        }
    )->name('profile.edit');
});

require __DIR__.'/auth.php';