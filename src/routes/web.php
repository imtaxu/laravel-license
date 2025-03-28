<?php

use Illuminate\Support\Facades\Route;
use Imtaxu\LaravelLicense\Controllers\LicenseController;

// License Status Route
Route::get('/', [LicenseController::class, 'status'])->name('license.status');

// License Activation Routes
Route::get('/activate', [LicenseController::class, 'showActivate'])->name('license.activate.form');
Route::post('/activate', [LicenseController::class, 'activate'])->name('license.activate');

// License Deactivation Route
Route::post('/deactivate', [LicenseController::class, 'deactivate'])->name('license.deactivate');

// License Error Route
Route::get('/error', [LicenseController::class, 'showError'])->name('license.error');

// License Verification Routes (New)
Route::get('/check', [LicenseController::class, 'checkLicense'])->name('license.check');
Route::get('/clear-cache', [LicenseController::class, 'clearCache'])->name('license.clear-cache');
