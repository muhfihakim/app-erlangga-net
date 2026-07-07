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
use App\Http\Controllers\Api\AssetController;
use App\Http\Controllers\Api\SupportingActivityController;
use App\Http\Controllers\OltController;

// Auth Routes
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Asset Management
    Route::get('/assets', [AssetController::class, 'index']);
    Route::post('/assets', [AssetController::class, 'store']);
    Route::get('/assets/transactions', [AssetController::class, 'transactions']);
    Route::post('/assets/transactions', [AssetController::class, 'storeTransaction']);
    Route::get('/assets/{id}', [AssetController::class, 'show']);
    Route::put('/assets/{id}', [AssetController::class, 'update']);
    Route::delete('/assets/{id}', [AssetController::class, 'destroy']);

    // Dashboard
    Route::get('/dashboard/stats', [DashboardController::class, 'stats']);

    // Pre-Order
    Route::get('/pre-orders', [PreOrderController::class, 'index']);
    Route::post('/pre-orders', [PreOrderController::class, 'store']);
    Route::get('/pre-orders/{id}', [PreOrderController::class, 'show']);
    Route::put('/pre-orders/{id}', [PreOrderController::class, 'update']);
    Route::post('/pre-orders/{id}/unspek', [PreOrderController::class, 'unspek']);
    Route::post('/pre-orders/{id}/approve-unspek', [PreOrderController::class, 'approveUnspek']);
    Route::post('/pre-orders/{id}/reopen', [PreOrderController::class, 'reopen']);
    Route::delete('/pre-orders/{id}', [PreOrderController::class, 'destroy']);

    // Customer
    Route::get('/customers', [CustomerController::class, 'index']);
    Route::post('/customers', [CustomerController::class, 'store']); // Activation Action
    Route::get('/customers/isolir', [CustomerController::class, 'isolir']);
    Route::get('/customers/{id}', [CustomerController::class, 'show']);
    Route::put('/customers/{id}', [CustomerController::class, 'update']);
    Route::delete('/customers/{id}', [CustomerController::class, 'destroy']);
    Route::post('/customers/{id}/restore', [CustomerController::class, 'restore']);

    // Maintenance
    Route::get('/maintenances', [MaintenanceController::class, 'index']);
    Route::post('/maintenances', [MaintenanceController::class, 'store']); // Admin Create Task
    Route::get('/maintenances/{id}', [MaintenanceController::class, 'show']);
    Route::post('/maintenances/{id}/complete', [MaintenanceController::class, 'complete']);
    Route::delete('/maintenances/{id}', [MaintenanceController::class, 'destroy']);

    // Dismantle
    Route::get('/dismantles', [DismantleController::class, 'index']);
    Route::post('/dismantles', [DismantleController::class, 'store']); // Admin Create Task
    Route::get('/dismantles/{id}', [DismantleController::class, 'show']);
    Route::post('/dismantles/{id}/complete', [DismantleController::class, 'complete']);
    Route::delete('/dismantles/{id}', [DismantleController::class, 'destroy']);

    // ODP Management
    Route::get('/odps', [OdpController::class, 'index']);
    Route::post('/odps', [OdpController::class, 'store']);
    Route::put('/odps/{id}', [OdpController::class, 'update']);
    Route::delete('/odps/{id}', [OdpController::class, 'destroy']);

    // Supporting Activity
    Route::apiResource('supporting', SupportingActivityController::class)->names('api.supporting');

    // Account Management (Admin Only)
    Route::middleware(['admin'])->group(function () {
        Route::get('/accounts', [AccountController::class, 'index']);
        Route::post('/accounts', [AccountController::class, 'store']);
        Route::get('/accounts/{id}', [AccountController::class, 'show']);
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
        Route::get('/pppoe/data', [\App\Http\Controllers\PppoeController::class, 'data']);
    });

    // OLT Management
    Route::prefix('olt')->group(function () {
        Route::get('/dashboard-data', [OltController::class, 'getDashboardData']);
        Route::get('/onu-details', [OltController::class, 'getOnuDetails']);
        Route::post('/check-next-id', [OltController::class, 'checkNextOnuId']);
        Route::post('/execute', [OltController::class, 'executeScript']);
    });

    // Location Tracking
    Route::get('/locations/all', [LocationController::class, 'getAllLocations']);
    Route::get('/locations/{user}', [LocationController::class, 'getUserLocation']);
    Route::post('/location/update', [LocationController::class, 'update']);
    
    // PTT Async Audio Upload
    Route::post('/ptt/upload', [\App\Http\Controllers\PttController::class, 'upload']);
});
