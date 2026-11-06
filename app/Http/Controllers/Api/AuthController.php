<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Pppoe;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    /**
     * Login Pelanggan Mobile App (Android / iOS)
     * Kredensial: ID Pelanggan (atau Username PPPoE) & Password PPPoE
     * 
     * Catatan: Ketika login gagal/salah, respons HTTP status tetap 200 OK 
     * dengan {"success": false, "message": "..."} agar tidak memicu 
     * unhandled HTTP error / interceptor 401 loop pada aplikasi mobile Flutter.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_pelanggan' => 'nullable|string',
            'username'     => 'nullable|string',
            'password'     => 'required|string',
            'device_name'  => 'nullable|string',
            'token_fcm'    => 'nullable|string',
            'fcm_token'    => 'nullable|string',
        ], [
            'password.required' => 'Kata sandi wajib diisi.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first() ?: 'Data login tidak lengkap.',
                'errors'  => $validator->errors()
            ], 200);
        }

        $identifier = trim($request->input('id_pelanggan') ?? $request->input('username') ?? '');

        if (empty($identifier)) {
            return response()->json([
                'success' => false,
                'message' => 'ID Pelanggan atau Username wajib diisi.',
                'errors'  => ['id_pelanggan' => ['ID Pelanggan atau Username wajib diisi.']]
            ], 200);
        }

        $password = (string) $request->input('password');
        $deviceName = $request->input('device_name', 'Mobile App');

        // Cari pelanggan berdasarkan id_pelanggan ATAU pppoe_username
        $customer = Customer::with(['paket', 'preOrder'])
            ->where(function ($q) use ($identifier) {
                $q->where('id_pelanggan', $identifier)
                  ->orWhere('pppoe_username', $identifier);
            })
            ->first();

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'ID Pelanggan atau Sandi salah.',
                'errors'  => ['id_pelanggan' => ['Akun pelanggan tidak ditemukan atau sandi salah.']]
            ], 200);
        }

        // Verifikasi password PPPoE pelanggan
        $expectedPassword = (string) ($customer->pppoe_password ?? '');
        if ($expectedPassword !== $password) {
            // Cek jika akun PPPoE memiliki password berbeda di tabel pppoe
            $pppoeRecord = $customer->pppoe;
            if (!$pppoeRecord || (string)$pppoeRecord->password !== $password) {
                return response()->json([
                    'success' => false,
                    'message' => 'ID Pelanggan atau Sandi salah.',
                    'errors'  => ['password' => ['Sandi yang Anda masukkan salah.']]
                ], 200);
            }
        }

        // Buat Sanctum API Token
        $token = $customer->createToken($deviceName)->plainTextToken;

        // Simpan token FCM jika disertakan saat login
        $fcmToken = trim($request->input('token_fcm') ?? $request->input('fcm_token') ?? '');
        if (!empty($fcmToken)) {
            $customer->update([
                'fcm_token'      => $fcmToken,
                'fcm_updated_at' => now(),
            ]);
        }

        $pppoe = $customer->pppoe;
        $unpaidInvoicesCount = 0;
        $latestUnpaidInvoice = null;

        if ($pppoe) {
            $unpaidInvoices = $pppoe->invoices()->where('status', 'unpaid')->orderBy('due_date', 'asc')->get();
            $unpaidInvoicesCount = $unpaidInvoices->count();
            $latestUnpaidInvoice = $unpaidInvoices->first();
        }

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil.',
            'token'   => $token,
            'data'    => [
                'customer' => [
                    'id'             => $customer->id,
                    'id_pelanggan'   => $customer->id_pelanggan,
                    'nama_lengkap'   => $customer->nama_lengkap,
                    'no_whatsapp'    => $customer->no_whatsapp,
                    'alamat_lengkap' => $customer->alamat_lengkap,
                    'jenis_modem'    => $customer->jenis_modem,
                    'sn_perangkat'   => $customer->sn_perangkat,
                ],
                'paket' => $customer->paket ? [
                    'id'          => $customer->paket->id,
                    'nama_paket'  => $customer->paket->nama_paket,
                    'kecepatan'   => $customer->paket->kecepatan ?? '-',
                    'harga'       => (float) $customer->paket->harga,
                    'total_harga' => (float) $customer->paket->total_harga,
                ] : null,
                'layanan' => [
                    'pppoe_username'       => $customer->pppoe_username,
                    'status_invoice'       => ($unpaidInvoicesCount > 0) ? 'belum_bayar' : 'terbayar',
                    'tanggal_aktif'        => ($pppoe && $pppoe->tanggal_aktif) ? ($pppoe->tanggal_aktif instanceof \DateTimeInterface ? $pppoe->tanggal_aktif->format('Y-m-d') : substr((string)$pppoe->tanggal_aktif, 0, 10)) : ($customer->created_at ? $customer->created_at->format('Y-m-d') : null),
                    'unpaid_invoices_count'=> $unpaidInvoicesCount,
                    'has_unpaid_bill'      => $unpaidInvoicesCount > 0,
                ]
            ]
        ], 200);
    }

    /**
     * Dapatkan data profil pelanggan yang sedang login
     */
    public function me(Request $request)
    {
        $customer = $request->user();

        if (!$customer instanceof Customer) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $customer->load(['paket', 'preOrder']);
        $pppoe = $customer->pppoe;

        return response()->json([
            'success' => true,
            'data'    => [
                'id'             => $customer->id,
                'id_pelanggan'   => $customer->id_pelanggan,
                'nama_lengkap'   => $customer->nama_lengkap,
                'no_whatsapp'    => $customer->no_whatsapp,
                'alamat_lengkap' => $customer->alamat_lengkap,
                'paket'          => $customer->paket ? [
                    'nama_paket'  => $customer->paket->nama_paket,
                    'kecepatan'   => $customer->paket->kecepatan,
                    'harga'       => (float) $customer->paket->harga,
                    'total_harga' => (float) $customer->paket->total_harga,
                ] : null,
                'pppoe_username'    => $customer->pppoe_username,
                'status_layanan'    => ($pppoe && $pppoe->is_isolir) ? 'Terisolir' : 'Aktif',
                'status_akun'       => ($pppoe && $pppoe->is_isolir) ? 'isolir' : 'aktif',
                'status_akun_badge' => ($pppoe && $pppoe->is_isolir) ? 'Terisolir' : 'Aktif',
                'is_isolir'         => (bool) ($pppoe && $pppoe->is_isolir),
                'tanggal_aktif'     => ($pppoe && $pppoe->tanggal_aktif) ? ($pppoe->tanggal_aktif instanceof \DateTimeInterface ? $pppoe->tanggal_aktif->format('Y-m-d') : substr((string)$pppoe->tanggal_aktif, 0, 10)) : ($customer->created_at ? $customer->created_at->format('Y-m-d') : null),
            ]
        ], 200);
    }

    /**
     * Logout pelanggan (Revoke API token)
     */
    public function logout(Request $request)
    {
        $user = $request->user();

        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout berhasil.'
        ], 200);
    }

    /**
     * Update Token FCM Pelanggan dari Mobile App (Android / iOS)
     */
    public function updateFcmToken(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'token_fcm' => 'nullable|string',
            'fcm_token' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first() ?: 'Data token tidak valid.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $fcmToken = trim($request->input('token_fcm') ?? $request->input('fcm_token') ?? '');
        $customer = $request->user();

        if (!$customer instanceof Customer) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized / Sesi tidak valid.',
            ], 401);
        }

        $customer->update([
            'fcm_token'      => !empty($fcmToken) ? $fcmToken : null,
            'fcm_updated_at' => !empty($fcmToken) ? now() : null,
        ]);

        return response()->json([
            'success'   => true,
            'message'   => !empty($fcmToken) ? 'Token FCM berhasil diperbarui.' : 'Token FCM berhasil dihapus.',
            'fcm_token' => $customer->fcm_token,
        ], 200);
    }
}
