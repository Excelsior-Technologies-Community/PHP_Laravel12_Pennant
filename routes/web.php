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
    | Existing Dashboard Feature Toggles
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
    | A/B Testing Conversion Tracker
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/features/track-conversion',
        [
            FeatureController::class,
            'trackConversion',
        ]
    )->name('features.track-conversion');

    /*
    |--------------------------------------------------------------------------
    | Feature Management Suite
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
    | Percentage Rollout & Traffic Slider
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/features/set-percentage',
        [
            FeatureController::class,
            'setPercentage',
        ]
    )->name('features.set-percentage');

    /*
    |--------------------------------------------------------------------------
    | Emergency Kill-Switch & Maintenance Mode
    |--------------------------------------------------------------------------
    */

    Route::post(
        '/features/kill-switch',
        [
            FeatureController::class,
            'killSwitch',
        ]
    )->name('features.kill-switch');

    Route::post(
        '/features/panic-kill-all',
        [
            FeatureController::class,
            'panicKillAll',
        ]
    )->name('features.panic-kill-all');

    Route::post(
        '/features/toggle-maintenance',
        [
            FeatureController::class,
            'toggleMaintenance',
        ]
    )->name('features.toggle-maintenance');

    /*
    |--------------------------------------------------------------------------
    | Bulk & Reset
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

    Route::post(
        '/features/reset-override',
        [
            FeatureController::class,
            'resetOverride',
        ]
    )->name('features.reset-override');

    /*
    |--------------------------------------------------------------------------
    | CSV Export & Audit History
    |--------------------------------------------------------------------------
    */

    Route::get(
        '/features/export',
        [
            FeatureController::class,
            'export',
        ]
    )->name('features.export');

    Route::get(
        '/features/audit',
        [
            FeatureAuditController::class,
            'index',
        ]
    )->name('features.audit');
});

use App\Http\Controllers\ProfileController;

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';