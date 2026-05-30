<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\PreOrderController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\MaintenanceController;
use App\Http\Controllers\Api\DismantleController;
use App\Http\Controllers\Api\OdpController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\FinanceController;
use App\Http\Controllers\Api\ActivityLogController;

// Auth Routes
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    // Pre-Order
    Route::get('/pre-orders', [PreOrderController::class, 'index']);
    Route::post('/pre-orders', [PreOrderController::class, 'store']);
    Route::get('/pre-orders/{id}', [PreOrderController::class, 'show']);
    Route::post('/pre-orders/{id}/delegate', [PreOrderController::class, 'delegate']);
    Route::post('/pre-orders/{id}/reset-delegation', [PreOrderController::class, 'resetDelegation']);
    Route::post('/pre-orders/{id}/unspek', [PreOrderController::class, 'unspek']);
    Route::post('/pre-orders/{id}/approve-unspek', [PreOrderController::class, 'approveUnspek']);
    Route::post('/pre-orders/{id}/reopen', [PreOrderController::class, 'reopen']);
    Route::delete('/pre-orders/{id}', [PreOrderController::class, 'destroy']);

    // Customer
    Route::get('/customers', [CustomerController::class, 'index']);
    Route::post('/customers', [CustomerController::class, 'store']); // Activation Action
    Route::get('/customers/isolir', [CustomerController::class, 'isolir']);
    Route::get('/customers/{id}', [CustomerController::class, 'show']);
    Route::delete('/customers/{id}', [CustomerController::class, 'destroy']);
    Route::post('/customers/{id}/restore', [CustomerController::class, 'restore']);

    // Maintenance
    Route::get('/maintenances', [MaintenanceController::class, 'index']);
    Route::post('/maintenances', [MaintenanceController::class, 'store']); // Admin Create Task
    Route::post('/maintenances/{id}/complete', [MaintenanceController::class, 'complete']);
    Route::post('/maintenances/{id}/reassign', [MaintenanceController::class, 'reassign']);
    Route::post('/maintenances/{id}/reset', [MaintenanceController::class, 'resetDelegation']);
    Route::delete('/maintenances/{id}', [MaintenanceController::class, 'destroy']);

    // Dismantle
    Route::get('/dismantles', [DismantleController::class, 'index']);
    Route::post('/dismantles', [DismantleController::class, 'store']); // Admin Create Task
    Route::post('/dismantles/{id}/complete', [DismantleController::class, 'complete']);
    Route::delete('/dismantles/{id}', [DismantleController::class, 'destroy']);

    // ODP Management
    Route::get('/odps', [OdpController::class, 'index']);
    Route::post('/odps', [OdpController::class, 'store']);
    Route::put('/odps/{id}', [OdpController::class, 'update']);
    Route::delete('/odps/{id}', [OdpController::class, 'destroy']);

    // Account Management (Admin Only)
    Route::middleware(['admin'])->group(function () {
        Route::get('/accounts', [AccountController::class, 'index']);
        Route::post('/accounts', [AccountController::class, 'store']);
        Route::put('/accounts/{id}', [AccountController::class, 'update']);
        Route::delete('/accounts/{id}', [AccountController::class, 'destroy']);
        Route::post('/accounts/{id}/2fa/reset', [AccountController::class, 'reset2FA']);
        
        // Activity Log
        Route::get('/activity-logs', [ActivityLogController::class, 'index']);
    });

    // Finance (Admin / Own Technician)
    Route::get('/finance', [FinanceController::class, 'index']);

    // MikroTik Monitoring (Real-time)
    Route::prefix('mikrotik')->group(function () {
        Route::get('/traffic', [\App\Http\Controllers\MonitoringController::class, 'traffic']);
        Route::get('/status', [\App\Http\Controllers\MonitoringController::class, 'status']);
        Route::get('/pppoe-active', [\App\Http\Controllers\PppoeController::class, 'active']);
    });

    // Location Tracking
    Route::get('/locations/all', [LocationController::class, 'getAllLocations']);
    Route::get('/locations/{user}', [LocationController::class, 'getUserLocation']);
    Route::post('/location/update', [LocationController::class, 'update']);
});
