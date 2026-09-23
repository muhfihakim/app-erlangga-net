<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\DeviceController;
use App\Http\Controllers\XenditWebhookController;

/*
|--------------------------------------------------------------------------
| API Routes - Aplikasi Mobile Pelanggan (Android & iOS)
|--------------------------------------------------------------------------
| 1. Login (Autentikasi ID Pelanggan & Password PPPoE)
| 2. Status Pembayaran & Melakukan Pembayaran (Payment Gateway Xendit)
| 3. Informasi Akun & Penggunaan (Traffic & Real-time Connection)
| 4. Kontrol Perangkat & Wi-Fi (GenieACS TR-069)
|--------------------------------------------------------------------------
*/

// --- 1. Autentikasi Pelanggan (Public) ---
Route::post('/login', [AuthController::class, 'login']);

// --- Protected Routes (Wajib Header: Authorization: Bearer <token>) ---
Route::middleware('auth:sanctum')->group(function () {
    
    // Auth Session
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // --- 2. Status Pembayaran & Payment Gateway ---
    Route::prefix('payments')->group(function () {
        Route::get('/status', [PaymentController::class, 'status']);                     // Ringkasan status & tagihan aktif
        Route::get('/invoices', [PaymentController::class, 'invoices']);                 // Daftar seluruh faktur / riwayat bayar
        Route::get('/invoices/{id}', [PaymentController::class, 'show']);                // Detail faktur tertentu
        Route::post('/pay', [PaymentController::class, 'pay']);                          // Melakukan pembayaran (Xendit URL)
        Route::match(['GET', 'POST'], '/invoices/{id}/check-status', [PaymentController::class, 'checkStatus']); // Cek/sinkronisasi status real-time
    });

    // --- 3. Informasi Akun & Penggunaan ---
    Route::prefix('account')->group(function () {
        Route::get('/info', [AccountController::class, 'info']);                         // Informasi Akun & Paket Langganan
        Route::get('/usage', [AccountController::class, 'usage']);                       // Informasi Penggunaan (Traffic Kuota & Uptime)
    });

    // --- 4. Kontrol Perangkat & Wi-Fi (GenieACS TR-069) ---
    Route::prefix('device')->group(function () {
        Route::get('/wifi', [DeviceController::class, 'wifi']);                          // Info Wi-Fi (SSID, Password, Status ONT)
        Route::get('/hosts', [DeviceController::class, 'hosts']);                        // Daftar Perangkat Terhubung (Connected Devices)
        Route::post('/wifi', [DeviceController::class, 'updateWifi']);                   // Ganti Nama & Sandi Wi-Fi
        Route::post('/reboot', [DeviceController::class, 'reboot']);                     // Reboot Perangkat ONT
    });
});

// --- Webhooks Payment Gateway (Public Callback) ---
Route::post('/webhooks/xendit', [XenditWebhookController::class, 'handleInvoice']);
Route::post('/webhooks/xendit/disbursements', [XenditWebhookController::class, 'handleDisbursement']);
