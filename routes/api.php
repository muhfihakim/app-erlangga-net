<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\MonitoringController;

// Auth Routes
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Rute API untuk lokasi (Sekarang dilindungi Sanctum)
    Route::get('/locations/all', [LocationController::class, 'getAllLocations']);
    Route::get('/locations/{user}', [LocationController::class, 'getUserLocation']);
    Route::post('/location/update', [LocationController::class, 'update']);
});

// MikroTik monitoring API (session-auth, not token-auth)
Route::middleware(['auth.custom'])->group(function () {
    Route::get('/mikrotik/traffic', [MonitoringController::class, 'traffic']);
    Route::get('/mikrotik/status', [MonitoringController::class, 'status']);
});
