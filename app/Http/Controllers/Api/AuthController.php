<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Pppoe;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Login Pelanggan Mobile App (Android / iOS)
     * Kredensial: ID Pelanggan (atau Username PPPoE) & Password PPPoE
     */
    public function login(Request $request)
    {
        $request->validate([
            'id_pelanggan' => 'nullable|string',
            'username'     => 'nullable|string',
            'password'     => 'required|string',
            'device_name'  => 'nullable|string',
        ]);

        $identifier = trim($request->input('id_pelanggan') ?? $request->input('username') ?? '');

        if (empty($identifier)) {
            return response()->json([
                'success' => false,
                'message' => 'ID Pelanggan wajib diisi.',
                'errors'  => ['id_pelanggan' => ['ID Pelanggan atau Username wajib diisi.']]
            ], 422);
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
                'errors'  => ['id_pelanggan' => ['Akun pelanggan tidak ditemukan.']]
            ], 401);
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
                ], 401);
            }
        }

        // Buat Sanctum API Token
        $token = $customer->createToken($deviceName)->plainTextToken;

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
                    'status_invoice'       => $pppoe->status_invoice ?? 'belum_bayar',
                    'tanggal_aktif'        => $pppoe->tanggal_aktif ? $pppoe->tanggal_aktif->format('Y-m-d') : null,
                    'unpaid_invoices_count'=> $unpaidInvoicesCount,
                    'has_unpaid_bill'      => $unpaidInvoicesCount > 0,
                ]
            ]
        ]);
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
                'pppoe_username' => $customer->pppoe_username,
                'status_layanan' => ($pppoe && $pppoe->status_invoice === 'terbayar') ? 'Aktif' : 'Menunggu Pembayaran',
                'tanggal_aktif'  => $pppoe && $pppoe->tanggal_aktif ? $pppoe->tanggal_aktif->format('Y-m-d') : null,
            ]
        ]);
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
        ]);
    }
}
