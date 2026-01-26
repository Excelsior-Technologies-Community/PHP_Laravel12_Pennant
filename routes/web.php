<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('/dashboard', 'dashboard')->name('dashboard');

    Route::post('/feature/on', [DashboardController::class, 'enableFeature'])->name('feature.on');
    Route::post('/feature/off', [DashboardController::class, 'disableFeature'])->name('feature.off');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', function () {
        return view('profile.edit');
    })->name('profile.edit');
});
require __DIR__.'/auth.php';
