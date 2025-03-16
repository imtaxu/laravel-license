<?php

use Illuminate\Support\Facades\Route;
use ImTaxu\LaravelLicense\Http\Controllers\LicenseController;

// Lisans doğrulama rotaları
Route::get('/license-error', [LicenseController::class, 'showError'])
    ->name('license.error');

Route::middleware(['web'])->group(function () {
    Route::get('/license/verify', [LicenseController::class, 'verify'])->name('license.verify');
    Route::post('/license/activate', [LicenseController::class, 'activate'])->name('license.activate');
    Route::post('/license/deactivate', [LicenseController::class, 'deactivate'])->name('license.deactivate');
});
