<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\ActivityLogController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DismantleController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MaintenanceController;
use App\Http\Controllers\MonitoringController;
use App\Http\Controllers\OdpController;
use App\Http\Controllers\SupportingActivityController;
use App\Http\Controllers\PreOrderController;
use App\Http\Controllers\PppoeController;
use App\Http\Controllers\PppoeAccountController;
use App\Http\Controllers\FinanceController;
use App\Http\Controllers\OltController;
use App\Http\Controllers\AssetController;
use App\Http\Controllers\IsolirController;

// Public Customer Isolation Portal (Self-Service Bill & Xendit Checkout)
Route::get('/isolir', [IsolirController::class, 'index'])->name('isolir.index');
Route::post('/isolir/search', [IsolirController::class, 'search'])->name('isolir.search');
Route::post('/isolir/{invoice}/pay', [IsolirController::class, 'payXendit'])->name('isolir.pay');
Route::get('/isolir/{invoice}/status', [IsolirController::class, 'checkStatus'])->name('isolir.status');

Route::get('/', function () {
    return redirect('/login');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/magic-login', [AuthController::class, 'magicLogin']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

Route::middleware(['auth.custom'])->group(function () {
    // Routes accessible by Admin and Teknisi
    Route::get('/pppoe', [PppoeController::class, 'index'])->name('pppoe.index');

    // Routes only for Administrator
    Route::middleware(['admin'])->group(function () {
        Route::get('/monitoring', [MonitoringController::class, 'index'])->name('monitoring.index');

        // Asset Management
        Route::prefix('assets')->name('assets.')->group(function () {
            Route::get('/', [AssetController::class, 'index'])->name('index');
            Route::post('/', [AssetController::class, 'store'])->name('store');
            Route::put('/{asset}', [AssetController::class, 'update'])->name('update');
            Route::delete('/{asset}', [AssetController::class, 'destroy'])->name('destroy');
            Route::get('/transactions', [AssetController::class, 'transactions'])->name('transactions');
            Route::post('/transactions', [AssetController::class, 'storeTransaction'])->name('transactions.store');
            Route::get('/export', [AssetController::class, 'export'])->name('export');
        });

        // Manage PPPoE Accounts
        Route::get('/pppoe-accounts', [\App\Http\Controllers\PppoeAccountController::class, 'index'])->name('pppoe_accounts.index');
        Route::post('/pppoe-accounts/sync-radius', [\App\Http\Controllers\PppoeAccountController::class, 'syncRadius'])->name('pppoe_accounts.sync_radius');
        Route::post('/pppoe-accounts', [\App\Http\Controllers\PppoeAccountController::class, 'store'])->name('pppoe_accounts.store');
        Route::put('/pppoe-accounts/{pppoe}', [\App\Http\Controllers\PppoeAccountController::class, 'update'])->name('pppoe_accounts.update');
        Route::delete('/pppoe-accounts/{pppoe}', [\App\Http\Controllers\PppoeAccountController::class, 'destroy'])->name('pppoe_accounts.destroy');
        
        // Manage Paket
        Route::get('/pakets', [\App\Http\Controllers\PaketController::class, 'index'])->name('pakets.index');
        Route::post('/pakets', [\App\Http\Controllers\PaketController::class, 'store'])->name('pakets.store');
        Route::put('/pakets/{paket}', [\App\Http\Controllers\PaketController::class, 'update'])->name('pakets.update');
        Route::delete('/pakets/{paket}', [\App\Http\Controllers\PaketController::class, 'destroy'])->name('pakets.destroy');
        
        // Invoices
        Route::get('/invoices', [\App\Http\Controllers\InvoiceController::class, 'index'])->name('invoices.index');

        // NAS / Router
        Route::resource('nas', \App\Http\Controllers\NasController::class)->except(['create', 'show', 'edit']);
        Route::get('/invoices/{pppoe}', [\App\Http\Controllers\InvoiceController::class, 'show'])->name('invoices.show');
        Route::get('/invoices/{invoice}/print', [\App\Http\Controllers\InvoiceController::class, 'print'])->name('invoices.print');
        Route::post('/invoices/generate', [\App\Http\Controllers\InvoiceController::class, 'generate'])->name('invoices.generate');
        Route::post('/invoices/{pppoe}/generate-single', [\App\Http\Controllers\InvoiceController::class, 'generateSingle'])->name('invoices.generate_single');
        Route::post('/invoices/{invoice}/pay-manual', [\App\Http\Controllers\InvoiceController::class, 'payManual'])->name('invoices.pay_manual');
        Route::post('/invoices/{invoice}/pay-xendit', [\App\Http\Controllers\InvoiceController::class, 'payXendit'])->name('invoices.pay_xendit');
        Route::post('/invoices/{invoice}/sync-xendit', [\App\Http\Controllers\InvoiceController::class, 'syncXendit'])->name('invoices.sync_xendit');
        // Transactions
        Route::get('/transactions', [\App\Http\Controllers\TransactionController::class, 'index'])->name('transactions.index');

        // Data Migration / Import
        Route::get('/migration', [\App\Http\Controllers\MigrationController::class, 'index'])->name('migration.index');
        Route::post('/migration/import', [\App\Http\Controllers\MigrationController::class, 'import'])->name('migration.import');

        // System Settings (MikroTik, OLT, RADIUS, Xendit)
        Route::get('/settings', [\App\Http\Controllers\SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings', [\App\Http\Controllers\SettingController::class, 'update'])->name('settings.update');
        Route::post('/settings/test-connection', [\App\Http\Controllers\SettingController::class, 'testConnection'])->name('settings.test_connection');
    });

    // Keuangan Teknisi
    Route::get('/keuangan/teknisi', [FinanceController::class, 'index'])->name('finance.index');
    Route::get('/keuangan/teknisi/export', [FinanceController::class, 'export'])->name('finance.export');

    // Kasbon Teknisi
    Route::get('/keuangan/kasbon', [App\Http\Controllers\KasbonController::class, 'index'])->name('kasbons.index');
    Route::post('/kasbons', [App\Http\Controllers\KasbonController::class, 'store'])->name('kasbons.store');
    Route::delete('/kasbons/{id}', [App\Http\Controllers\KasbonController::class, 'destroy'])->name('kasbons.destroy');

    // Rute untuk FTTH - Manajemen ODP
    Route::get('/odp', [OdpController::class, 'index'])->name('odp.index');
    Route::post('/odp', [OdpController::class, 'store'])->name('odp.store');
    Route::put('/odp/{id}', [OdpController::class, 'update'])->name('odp.update');
    Route::delete('/odp/{id}', [OdpController::class, 'destroy'])->name('odp.destroy');

    Route::get('/olt', [OltController::class, 'index'])->name('olt.index');

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboards.index');
    Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index');
    Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.store');
    Route::put('/accounts/{user}', [AccountController::class, 'update'])->name('accounts.update');
    Route::post('/accounts/{user}/toggle-status', [AccountController::class, 'toggleStatus'])->name('accounts.toggleStatus');
    Route::delete('/accounts/{user}', [AccountController::class, 'destroy'])->name('accounts.destroy');
    Route::get('/accounts/{user}/2fa/show', [AccountController::class, 'show2FA'])->name('admin.users.2fa.show');
    Route::post('/accounts/{user}/2fa/reset', [AccountController::class, 'reset2FA'])->name('admin.users.2fa.reset');
    Route::get('/activity-log', [ActivityLogController::class, 'index'])->name('activitylog.index');
    Route::get('/activity-logs/notifications', [ActivityLogController::class, 'getNotifications'])->name('activitylog.notifications');
    Route::get('/activity-logs/export', [ActivityLogController::class, 'export'])->name('activitylog.export');

    Route::get('/laporan/preorder', [ActivityLogController::class, 'laporanPreOrder'])->name('activitylog.preorder');
    Route::get('/laporan/preorder/export', [ActivityLogController::class, 'exportPreOrder'])->name('activitylog.preorder.export');

    Route::get('/laporan/aktivasi', [ActivityLogController::class, 'laporanAktivasi'])->name('activitylog.aktivasi');
    Route::get('/laporan/aktivasi/export', [ActivityLogController::class, 'exportAktivasi'])->name('activitylog.aktivasi.export');

    Route::get('/laporan/aktivasi-sales', [ActivityLogController::class, 'laporanAktivasiSales'])->name('activitylog.aktivasi_sales');
    Route::get('/laporan/aktivasi-sales/export', [ActivityLogController::class, 'exportAktivasiSales'])->name('activitylog.aktivasi_sales.export');

    // Laporan Maintenance (Selesai)
    Route::get('/laporan/maintenance', [ActivityLogController::class, 'laporanMaintenance'])->name('activitylog.maintenance');
    Route::get('/laporan/maintenance/export', [ActivityLogController::class, 'exportMaintenance'])->name('activitylog.maintenance.export');

    Route::get('/laporan/dismantle', [ActivityLogController::class, 'laporanDismantle'])->name('activitylog.dismantle');
    Route::get('/laporan/dismantle/export', [ActivityLogController::class, 'exportDismantle'])->name('activitylog.dismantle.export');

    Route::get('/maintenances', [MaintenanceController::class, 'index'])->name('maintenances.index');
    Route::get('/maintenances/customer/{id}', [MaintenanceController::class, 'getCustomerData']);
    Route::post('/maintenances', [MaintenanceController::class, 'store'])->name('maintenances.store');
    Route::post('/maintenances/{id}/complete', [MaintenanceController::class, 'complete'])->name('maintenances.complete');
    Route::delete('/maintenances/{id}', [MaintenanceController::class, 'destroy'])->name('maintenances.destroy');

    Route::get('/dismantles', [DismantleController::class, 'index'])->name('dismantles.index');
    Route::get('/dismantles/customer/{id}', [DismantleController::class, 'getCustomerData']);
    Route::post('/dismantles', [DismantleController::class, 'store'])->name('dismantles.store');
    Route::post('/dismantles/{id}/complete', [DismantleController::class, 'complete'])->name('dismantles.complete');
    Route::delete('/dismantles/{id}', [DismantleController::class, 'destroy'])->name('dismantles.destroy');
    Route::delete('/dismantles/customer/{id}', [DismantleController::class, 'deleteCustomer']);

    Route::post('/location/update', [LocationController::class, 'update'])->name('location.update');
    Route::get('/tracking', [LocationController::class, 'showTrackingMap'])->name('accounts.tracking');
    Route::get('/locations/all', [LocationController::class, 'getAllLocations'])->name('api.locations.all');
    Route::get('/locations/{user}', [LocationController::class, 'getUserLocation'])->name('api.locations.user');

    // Rute untuk Pre-Order
    Route::get('/pre-orders', [PreOrderController::class, 'index'])->name('pre_orders.index');
    Route::post('/pre-orders', [PreOrderController::class, 'store'])->name('pre_orders.store');
    Route::put('/pre-orders/{preOrder}', [PreOrderController::class, 'update'])->name('pre_orders.update');
    Route::delete('/pre-orders/{preOrder}', [PreOrderController::class, 'destroy'])->name('pre_orders.destroy');
    Route::post('pre-orders/{preOrder}/unspek', [PreOrderController::class, 'unspek'])->name('pre_orders.unspek');
    Route::post('pre-orders/{preOrder}/approve-unspek', [PreOrderController::class, 'approveUnspek'])->name('pre_orders.approve_unspek');
    Route::post('pre-orders/{preOrder}/reopen-unspek', [PreOrderController::class, 'reopenUnspek'])->name('pre_orders.reopen_unspek');

    // Rute untuk Modul Pelanggan Aktif (Akses untuk Sales & Admin)
    Route::get('customers/active-list', [CustomerController::class, 'activeList'])->name('customers.active_list');
    Route::get('customers/isolir/export', [CustomerController::class, 'exportIsolir'])->name('customers.export_isolir');
    Route::post('/customers/{id}/restore', [CustomerController::class, 'restore'])->name('customers.restore');

    // PPPoE APIs (Accessible by Admin and Teknisi)
    Route::prefix('api')->group(function () {
        Route::get('/pppoe/active', [PppoeController::class, 'active']);
        Route::get('/pppoe/data', [PppoeController::class, 'data'])->name('pppoe.data');
        Route::get('/pppoe/realtime-traffic', [PppoeController::class, 'traffic'])->name('pppoe.traffic');
    });

    // MikroTik & Admin APIs (Administrator only)
    Route::prefix('api')->middleware(['admin'])->group(function () {
        Route::get('/mikrotik/traffic', [MonitoringController::class, 'traffic']);
        Route::get('/mikrotik/status', [MonitoringController::class, 'status']);
        Route::get('/mikrotik/pppoe-logs', [MonitoringController::class, 'pppoeLogs']);
        
        // OLT Telnet API Routes (Admin only: Generator)
        Route::post('/olt/check-next-id', [OltController::class, 'checkNextOnuId']);
        Route::post('/olt/connect', [OltController::class, 'connectTerminal']);
        Route::post('/olt/verify-login', [OltController::class, 'verifyLogin']);
        Route::post('/olt/execute', [OltController::class, 'executeScript']);
    });

    // OLT Monitoring APIs (Accessible by Admin & Teknisi)
    Route::prefix('api/olt')->group(function () {
        Route::get('/dashboard-data', [OltController::class, 'getDashboardData']);
        Route::get('/onu-details', [OltController::class, 'getOnuDetails']);
    });

    // PPPoE Remote Tunnel (Accessible by Admin & Teknisi)
    Route::post('/api/pppoe/remote-tunnel', [PppoeController::class, 'remoteTunnel'])->name('pppoe.remote_tunnel');

    Route::post('/paket/sync-mikrotik', [App\Http\Controllers\PaketController::class, 'syncFromMikrotik'])->name('paket.sync');
    Route::resource('paket', \App\Http\Controllers\PaketController::class)->except(['create', 'edit', 'show']);
    
    Route::resource('customers', CustomerController::class)->except(['create', 'edit', 'show']);
    
    Route::resource('supporting', SupportingActivityController::class);
    Route::get('supporting-export', [SupportingActivityController::class, 'export'])->name('supporting.export');

    // PTT Async Audio Upload
    // Route::post('/api/ptt/upload', [\App\Http\Controllers\PttController::class, 'upload'])->name('ptt.upload');

    // Migrasi & Pemulihan Data Radboox
    Route::get('/migration', [\App\Http\Controllers\MigrationController::class, 'index'])->name('migration.index');
    Route::post('/migration/import', [\App\Http\Controllers\MigrationController::class, 'import'])->name('migration.import');
    Route::post('/migration/reset', [\App\Http\Controllers\MigrationController::class, 'reset'])->name('migration.reset');
});
