<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocationController;

// Rute API untuk lokasi
Route::get('/locations/all', [LocationController::class, 'getAllLocations']);
Route::get('/locations/{user}', [LocationController::class, 'getUserLocation']);
