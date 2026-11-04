<?php

namespace App\Http\Controllers;

use App\Models\BroadcastNotification;
use App\Models\Customer;
use App\Models\Paket;
use App\Services\FcmService;
use Illuminate\Http\Request;

class BroadcastNotificationController extends Controller
{
    /**
     * Tampilkan halaman daftar riwayat broadcast & form pengiriman pengumuman
     */
    public function index(Request $request)
    {
        $search = trim($request->input('search', ''));
        $typeFilter = $request->input('type');

        $query = BroadcastNotification::with('creator')->latest('id');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('body', 'like', "%{$search}%");
            });
        }

        if (!empty($typeFilter)) {
            $query->where('type', $typeFilter);
        }

        $broadcasts = $query->paginate(15)->withQueryString();

        // Statistik ringkasan
        $stats = [
            'total_broadcasts' => BroadcastNotification::count(),
            'total_sent'       => (int) BroadcastNotification::sum('total_sent'),
            'total_targeted'   => (int) BroadcastNotification::sum('total_targeted'),
            'fcm_registered'   => Customer::whereNotNull('fcm_token')->where('fcm_token', '!=', '')->count(),
            'total_customers'  => Customer::count(),
        ];

        $pakets = Paket::orderBy('nama_paket')->get(['id', 'nama_paket', 'kecepatan', 'harga']);
        $customers = Customer::orderBy('nama_lengkap')->get(['id', 'id_pelanggan', 'nama_lengkap', 'fcm_token']);

        return view('notifications.broadcast', compact('broadcasts', 'stats', 'pakets', 'customers', 'search', 'typeFilter'));
    }

    /**
     * Kirim broadcast notifikasi baru ke pelanggan (push FCM & in-app)
     */
    public function store(Request $request, FcmService $fcmService)
    {
        $request->validate([
            'title'       => 'required|string|max:150',
            'body'        => 'required|string|max:2000',
            'type'        => 'required|in:maintenance,announcement,promo,billing,general',
            'target_type' => 'required|in:all,package,customer',
            'target_id'   => 'nullable|integer',
        ], [
            'title.required' => 'Judul pemberitahuan wajib diisi.',
            'body.required'  => 'Isi pesan pemberitahuan wajib diisi.',
            'type.required'  => 'Tipe pemberitahuan wajib dipilih.',
        ]);

        $targetType = $request->input('target_type', 'all');
        $targetId = $request->filled('target_id') ? (int) $request->input('target_id') : null;

        // Validasi target id jika memilih paket atau customer
        if ($targetType === 'package' && empty($targetId)) {
            return back()->withInput()->with('error', 'Silakan pilih paket langganan yang menjadi target.');
        }

        if ($targetType === 'customer' && empty($targetId)) {
            return back()->withInput()->with('error', 'Silakan pilih pelanggan yang menjadi target.');
        }

        $result = $fcmService->broadcast(
            title: $request->input('title'),
            body: $request->input('body'),
            type: $request->input('type'),
            targetType: $targetType,
            targetId: $targetId,
            createdBy: auth()->id()
        );

        return redirect()->route('broadcasts.index')->with(
            'success',
            "Pemberitahuan berhasil diproses! {$result['total_targeted']} pelanggan ditargetkan, {$result['total_sent']} notifikasi FCM push terkirim."
        );
    }

    /**
     * Hapus riwayat log broadcast
     */
    public function destroy(BroadcastNotification $broadcast)
    {
        $broadcast->delete();
        return redirect()->route('broadcasts.index')->with('success', 'Riwayat pemberitahuan berhasil dihapus.');
    }
}
