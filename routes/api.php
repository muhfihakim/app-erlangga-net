<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\Api\AuthController;

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
