<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\ActivityLogController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DismantleController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\OdpController;
use App\Http\Controllers\PreOrderController;
use App\Http\Controllers\TwoFactorController;
use SebastianBergmann\CodeCoverage\Report\Html\Dashboard;

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
// Route::get('/2fa/verify', [TwoFactorController::class, 'showVerifyForm'])->name('2fa.verify');
// Route::post('/2fa/verify', [TwoFactorController::class, 'verify']);
// Route::get('/2fa/setup', [TwoFactorController::class, 'show2faForm'])->name('2fa.setup')->middleware('auth');
// Route::post('/2fa/reset', [TwoFactorController::class, 'reset'])->name('2fa.reset')->middleware('auth');
// Route::get('/2fa/redirect-to-verify', function () {
//     session(['2fa:user:id' => auth()->id()]);
//     return redirect()->route('2fa.verify');
// })->middleware('auth')->name('2fa.redirect');

Route::middleware(['auth.custom'])->group(function () {
    // Rute untuk FTTH - Manajemen ODP
    Route::get('/odp', [OdpController::class, 'index'])->name('odp.index');
    Route::post('/odp', [OdpController::class, 'store'])->name('odp.store');
    Route::put('/odp/{id}', [OdpController::class, 'update'])->name('odp.update');
    Route::delete('/odp/{id}', [OdpController::class, 'destroy'])->name('odp.destroy');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboards.index');
    Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
    Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.store');
    Route::put('/accounts/{user}', [AccountController::class, 'update'])->name('accounts.update');
    Route::delete('/accounts/{user}', [AccountController::class, 'destroy'])->name('accounts.destroy');
    Route::get('/accounts/{user}/2fa/show', [AccountController::class, 'show2FA'])->name('admin.users.2fa.show');
    Route::post('/accounts/{user}/2fa/reset', [AccountController::class, 'reset2FA'])->name('admin.users.2fa.reset');
    Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activitylog.index');
    Route::get('/activity-logs/export', [ActivityLogController::class, 'export'])->name('activitylog.export');

    Route::get('/laporan/preorder', [ActivityLogController::class, 'laporanPreOrder'])->name('activitylog.preorder');
    Route::get('/laporan/preorder/export', [ActivityLogController::class, 'exportPreOrder'])->name('activitylog.preorder.export');

    Route::get('/laporan/preorder-expired', [ActivityLogController::class, 'laporanExpired'])->name('activitylog.expired');
    Route::get('/laporan/preorder-expired/export', [ActivityLogController::class, 'exportExpired'])->name('activitylog.expired.export');

    Route::get('/laporan/aktivasi', [ActivityLogController::class, 'laporanAktivasi'])->name('activitylog.aktivasi');
    Route::get('/laporan/aktivasi/export', [ActivityLogController::class, 'exportAktivasi'])->name('activitylog.aktivasi.export');

    // Laporan Maintenance (Selesai)
    Route::get('/laporan/maintenance', [ActivityLogController::class, 'laporanMaintenance'])->name('activitylog.maintenance');
    Route::get('/laporan/maintenance/export', [ActivityLogController::class, 'exportMaintenance'])->name('activitylog.maintenance.export');

    // Laporan Maintenance Expired
    Route::get('/laporan/maintenance-expired', [ActivityLogController::class, 'laporanMaintenanceExpired'])->name('activitylog.maintenance_expired');
    Route::get('/laporan/maintenance-expired/export', [ActivityLogController::class, 'exportMaintenanceExpired'])->name('activitylog.maintenance_expired.export');

    Route::get('/laporan/dismantle', [ActivityLogController::class, 'laporanDismantle'])->name('activitylog.dismantle');
    Route::get('/laporan/dismantle/export', [ActivityLogController::class, 'exportDismantle'])->name('activitylog.dismantle.export');

    Route::get('/maintenances', [MaintenanceController::class, 'index'])->name('maintenances.index');
    Route::get('/maintenances/customer/{id}', [MaintenanceController::class, 'getCustomerData']);
    Route::post('/maintenances', [MaintenanceController::class, 'store'])->name('maintenances.store');
    Route::post('/maintenances/{id}/complete', [MaintenanceController::class, 'complete'])->name('maintenances.complete');
    Route::post('/maintenances/{maintenance}/reset', [MaintenanceController::class, 'resetDelegation'])->name('maintenances.reset');
    Route::post('/maintenances/{id}/reassign', [MaintenanceController::class, 'reassign'])->name('maintenances.reassign');
    Route::delete('/maintenances/{id}', [MaintenanceController::class, 'destroy'])->name('maintenances.destroy');

    Route::get('/dismantles', [DismantleController::class, 'index'])->name('dismantles.index');
    Route::get('/dismantles/customer/{id}', [DismantleController::class, 'getCustomerData']);
    Route::post('/dismantles', [DismantleController::class, 'store'])->name('dismantles.store');
    Route::post('/dismantles/{id}/complete', [DismantleController::class, 'complete'])->name('dismantles.complete');
    Route::delete('/dismantles/{id}', [DismantleController::class, 'destroy'])->name('dismantles.destroy');
    Route::delete('/dismantles/customer/{id}', [DismantleController::class, 'deleteCustomer'])->name('dismantles.deleteCustomer');

    Route::post('/location/update', [LocationController::class, 'update'])->name('location.update');
    Route::get('/tracking', [LocationController::class, 'showTrackingMap'])->name('accounts.tracking');
    Route::get('/locations/all', [LocationController::class, 'getAllLocations'])->name('api.locations.all');
    Route::get('/locations/{user}', [LocationController::class, 'getUserLocation'])->name('api.locations.user');

    // Rute untuk Pre-Order
    Route::get('/pre-orders', [PreOrderController::class, 'index'])->name('pre_orders.index');
    Route::post('/pre-orders', [PreOrderController::class, 'store'])->name('pre_orders.store');
    Route::put('/pre-orders/{preOrder}', [PreOrderController::class, 'update'])->name('pre_orders.update');
    Route::delete('/pre-orders/{preOrder}', [PreOrderController::class, 'destroy'])->name('pre_orders.destroy');
    Route::post('pre-orders/{preOrder}/delegate', [PreOrderController::class, 'delegate'])->name('pre_orders.delegate');
    Route::post('pre-orders/{preOrder}/reset-delegation', [PreOrderController::class, 'resetDelegation'])->name('pre_orders.reset');

    // Rute untuk Modul Pelanggan Aktif (Akses untuk Sales & Admin)
    Route::get('customers/active-list', [CustomerController::class, 'activeList'])->name('customers.active_list');
    Route::post('/customers/{id}/restore', [CustomerController::class, 'restore'])->name('customers.restore');


    Route::resource('customers', CustomerController::class)->except(['create', 'edit', 'show']);
});
