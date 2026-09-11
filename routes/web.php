<?php

use App\Http\Controllers\IcuAndroidReportController;
use App\Http\Controllers\IcuProbeController;
use App\Http\Controllers\PhpInfoController;
use Illuminate\Support\Facades\Route;

// index route is handled by Filament

Route::middleware('auth')->group(function (): void {
    Route::get('/diagnostics/php-info', PhpInfoController::class)
        ->name('diagnostics.php-info');

    Route::get('/diagnostics/icu-probe', IcuProbeController::class)
        ->name('diagnostics.icu-probe');

    Route::get('/diagnostics/intl-check', IcuAndroidReportController::class)
        ->name('diagnostics.icu-check');
});
