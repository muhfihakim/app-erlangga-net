<?php

namespace App\Http\Controllers;

use App\Models\BankAccount;
use App\Models\Setting;
use App\Models\Withdrawal;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WithdrawalController extends Controller
{
    /**
     * Tampilkan halaman penarikan saldo Xendit Super Admin
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Akses Ditolak: Fitur penarikan saldo (Disbursement) khusus untuk Super Admin.');
        }

        $balance = $this->getXenditBalance();
        $banks = $this->getAvailableBanks();
        $bankAccounts = BankAccount::orderBy('is_default', 'desc')->orderBy('bank_name', 'asc')->get();
        
        $withdrawals = Withdrawal::with('user')
            ->orderBy('id', 'desc')
            ->paginate(15);

        $totalCompleted = Withdrawal::where('status', 'COMPLETED')->sum('amount');
        $totalPending = Withdrawal::where('status', 'PENDING')->sum('amount');

        return view('withdrawals.index', compact(
            'balance',
            'banks',
            'bankAccounts',
            'withdrawals',
            'totalCompleted',
            'totalPending'
        ));
    }

    /**
     * Kirim kode OTP untuk verifikasi penarikan dana
     */
    public function sendOtp(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'amount' => 'required|numeric|min:10000',
            'bank_code' => 'required|string',
            'account_number' => 'required|string',
            'account_holder_name' => 'required|string',
        ]);

        $otp = (string) random_int(100000, 999999);
        $cacheKey = 'withdrawal_otp_' . $user->id;

        // Simpan OTP di cache selama 5 menit (300 detik)
        Cache::put($cacheKey, [
            'otp' => $otp,
            'amount' => $request->amount,
            'bank_code' => $request->bank_code,
            'account_number' => $request->account_number,
            'account_holder_name' => $request->account_holder_name,
            'description' => $request->description ?? 'Penarikan Saldo Xendit',
            'created_at' => now(),
        ], 300);

        Log::info("Super Admin Withdrawal OTP generated for user #{$user->id}: {$otp}");

        // Format info pengiriman
        $targetEmail = $user->email;
        $maskedEmail = $this->maskEmail($targetEmail);

        return response()->json([
            'success' => true,
            'message' => "Kode OTP 6-digit berhasil dikirimkan ke {$maskedEmail}.",
            'recipient' => $maskedEmail,
            'debug_otp' => config('app.debug') ? $otp : null, // Hanya muncul saat debug mode aktif
            'expires_in' => 300,
        ]);
    }

    /**
     * Eksekusi penarikan dana setelah OTP diverifikasi
     */
    public function withdraw(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        $request->validate([
            'otp' => 'required|string|size:6',
            'amount' => 'required|numeric|min:10000',
            'bank_code' => 'required|string',
            'account_number' => 'required|string',
            'account_holder_name' => 'required|string',
            'description' => 'nullable|string|max:200',
        ]);

        $cacheKey = 'withdrawal_otp_' . $user->id;
        $cachedData = Cache::get($cacheKey);

        if (!$cachedData || $cachedData['otp'] !== trim($request->otp)) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP yang Anda masukkan salah atau sudah kadaluarsa (maks 5 menit).'
            ], 422);
        }

        $secretKey = Setting::get('xendit_secret_key', config('services.xendit.secret_key'));
        if (empty($secretKey)) {
            return response()->json([
                'success' => false,
                'message' => 'Kunci API Xendit belum dikonfigurasi di menu Pengaturan Sistem.'
            ], 400);
        }

        // Cek saldo terkini
        $currentBalance = $this->getXenditBalance();
        if ($currentBalance < (float) $request->amount) {
            return response()->json([
                'success' => false,
                'message' => 'Saldo Xendit tidak mencukupi untuk penarikan sebesar Rp ' . number_format($request->amount, 0, ',', '.') . '. Saldo saat ini: Rp ' . number_format($currentBalance, 0, ',', '.')
            ], 400);
        }

        $referenceId = 'WD-' . date('YmdHis') . '-' . strtoupper(Str::random(4));
        $bankName = $this->getBankNameByCode($request->bank_code);

        // Buat record Withdrawal di database lokal
        $withdrawal = Withdrawal::create([
            'user_id' => $user->id,
            'reference_id' => $referenceId,
            'amount' => $request->amount,
            'bank_code' => strtoupper($request->bank_code),
            'bank_name' => $bankName,
            'account_number' => $request->account_number,
            'account_holder_name' => strtoupper($request->account_holder_name),
            'description' => $request->description ?? 'Penarikan Saldo XenPlatform',
            'status' => 'PENDING',
            'approved_at' => now(),
        ]);

        // Eksekusi Payout ke Xendit API
        try {
            $idempotencyKey = 'IDEM-' . $referenceId;
            $response = Http::withBasicAuth($secretKey, '')
                ->withHeaders([
                    'X-IDEMPOTENCY-KEY' => $idempotencyKey,
                    'Content-Type' => 'application/json',
                ])
                ->timeout(20)
                ->post('https://api.xendit.co/disbursements', [
                    'external_id' => $referenceId,
                    'amount' => (int) $request->amount,
                    'bank_code' => strtoupper($request->bank_code),
                    'account_holder_name' => strtoupper($request->account_holder_name),
                    'account_number' => (string) $request->account_number,
                    'description' => $request->description ?? 'Penarikan Saldo Erlangga Net',
                ]);

            if ($response->successful()) {
                $resData = $response->json();
                $status = strtoupper($resData['status'] ?? 'PENDING');
                
                $withdrawal->update([
                    'xendit_disbursement_id' => $resData['id'] ?? null,
                    'status' => $status === 'COMPLETED' ? 'COMPLETED' : 'PENDING',
                ]);

                // Hapus OTP setelah berhasil
                Cache::forget($cacheKey);

                return response()->json([
                    'success' => true,
                    'message' => 'Permintaan penarikan dana berhasil diproses oleh Xendit!',
                    'data' => $withdrawal,
                ]);
            } else {
                $errData = $response->json();
                $errorMessage = $errData['message'] ?? 'Gagal memproses penarikan di gateway Xendit.';
                $errorCode = $errData['error_code'] ?? 'DISBURSEMENT_FAILED';

                $withdrawal->update([
                    'status' => 'FAILED',
                    'failure_code' => $errorCode . ': ' . $errorMessage,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Xendit Error: ' . $errorMessage
                ], 400);
            }
        } catch (\Throwable $e) {
            Log::error('Disbursement Execution Error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'withdrawal_id' => $withdrawal->id,
            ]);

            $withdrawal->update([
                'status' => 'FAILED',
                'failure_code' => 'EXCEPTION: ' . $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem saat menghubungi server Xendit: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Sinkronisasi status penarikan dari Xendit API
     */
    public function syncStatus(Withdrawal $withdrawal)
    {
        $user = auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
        }

        if (!$withdrawal->xendit_disbursement_id) {
            return response()->json(['success' => false, 'message' => 'Transaksi ini belum memiliki ID Disbursement Xendit.'], 400);
        }

        $secretKey = Setting::get('xendit_secret_key', config('services.xendit.secret_key'));

        try {
            $response = Http::withBasicAuth($secretKey, '')
                ->timeout(15)
                ->get("https://api.xendit.co/disbursements/{$withdrawal->xendit_disbursement_id}");

            if ($response->successful()) {
                $data = $response->json();
                $status = strtoupper($data['status'] ?? $withdrawal->status);

                $withdrawal->update([
                    'status' => in_array($status, ['COMPLETED', 'SUCCESS']) ? 'COMPLETED' : ($status === 'FAILED' ? 'FAILED' : 'PENDING'),
                    'failure_code' => $data['failure_code'] ?? $withdrawal->failure_code,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Status penarikan berhasil diperbarui: ' . $withdrawal->status,
                    'status' => $withdrawal->status,
                ]);
            }

            return response()->json(['success' => false, 'message' => 'Gagal mengambil data dari Xendit.'], 400);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Ambil saldo aktif Xendit (CASH Balance)
     */
    private function getXenditBalance(): float
    {
        $secretKey = Setting::get('xendit_secret_key', config('services.xendit.secret_key'));
        if (empty($secretKey)) {
            return 0.0;
        }

        try {
            $response = Http::withBasicAuth($secretKey, '')
                ->timeout(10)
                ->get('https://api.xendit.co/balance?account_type=CASH');

            if ($response->successful()) {
                $data = $response->json();
                return (float) ($data['balance'] ?? 0.0);
            }
        } catch (\Throwable $e) {
            Log::warning('Gagal mengambil saldo Xendit: ' . $e->getMessage());
        }

        return 0.0;
    }

    /**
     * Daftar bank yang didukung
     */
    private function getAvailableBanks(): array
    {
        $defaultBanks = [
            ['code' => 'BCA', 'name' => 'Bank Central Asia (BCA)'],
            ['code' => 'BRI', 'name' => 'Bank Rakyat Indonesia (BRI)'],
            ['code' => 'MANDIRI', 'name' => 'Bank Mandiri'],
            ['code' => 'BNI', 'name' => 'Bank Negara Indonesia (BNI)'],
            ['code' => 'BSI', 'name' => 'Bank Syariah Indonesia (BSI)'],
            ['code' => 'CIMB', 'name' => 'Bank CIMB Niaga'],
            ['code' => 'PERMATA', 'name' => 'Bank Permata'],
            ['code' => 'DANAMON', 'name' => 'Bank Danamon'],
            ['code' => 'BTPN', 'name' => 'Bank BTPN / Jenius'],
            ['code' => 'JAGO', 'name' => 'Bank Jago'],
            ['code' => 'SEABANK', 'name' => 'SeaBank Indonesia'],
            ['code' => 'NEO', 'name' => 'Bank Neo Commerce (BNC)'],
            ['code' => 'OVO', 'name' => 'E-Wallet OVO'],
            ['code' => 'GOPAY', 'name' => 'E-Wallet GoPay'],
            ['code' => 'DANA', 'name' => 'E-Wallet DANA'],
            ['code' => 'SHOPEEPAY', 'name' => 'E-Wallet ShopeePay'],
        ];

        $secretKey = Setting::get('xendit_secret_key', config('services.xendit.secret_key'));
        if (!empty($secretKey)) {
            try {
                $response = Http::withBasicAuth($secretKey, '')
                    ->timeout(8)
                    ->get('https://api.xendit.co/available_disbursements_banks');

                if ($response->successful()) {
                    $apiBanks = $response->json();
                    if (is_array($apiBanks) && count($apiBanks) > 0) {
                        return array_map(function ($b) {
                            return [
                                'code' => $b['code'],
                                'name' => $b['name'],
                            ];
                        }, $apiBanks);
                    }
                }
            } catch (\Throwable $e) {
                // Fallback to default banks
            }
        }

        return $defaultBanks;
    }

    /**
     * Helper nama bank berdasarkan kode
     */
    private function getBankNameByCode(string $code): string
    {
        $banks = $this->getAvailableBanks();
        foreach ($banks as $bank) {
            if (strtoupper($bank['code']) === strtoupper($code)) {
                return $bank['name'];
            }
        }
        return strtoupper($code);
    }

    /**
     * Mask email (cth: ad***@erlangga.net)
     */
    private function maskEmail(string $email): string
    {
        $parts = explode('@', $email);
        if (count($parts) !== 2) return $email;
        $name = $parts[0];
        $domain = $parts[1];
        $len = strlen($name);
        if ($len <= 2) {
            return $name . '***@' . $domain;
        }
        return substr($name, 0, 2) . str_repeat('*', max(3, $len - 2)) . '@' . $domain;
    }
}
