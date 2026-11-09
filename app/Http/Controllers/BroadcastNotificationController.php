<?php

namespace App\Http\Controllers;

use App\Models\BroadcastNotification;
use App\Models\Customer;
use App\Models\Paket;
use App\Services\FcmService;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class BroadcastNotificationController extends Controller
{
    /**
     * Tampilkan halaman daftar riwayat broadcast & form pengiriman pengumuman
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = BroadcastNotification::with('creator')->select('broadcast_notifications.*');

            if ($type = $request->input('type')) {
                $query->where('broadcast_notifications.type', $type);
            }

            if ($targetType = $request->input('target_type')) {
                $query->where('broadcast_notifications.target_type', $targetType);
            }

            if ($search = $request->input('search.value')) {
                $cleanSearch = trim($search);
                $query->where(function ($q) use ($cleanSearch) {
                    $q->where('title', 'like', "%{$cleanSearch}%")
                      ->orWhere('body', 'like', "%{$cleanSearch}%");
                });
            }

            $pakets = Paket::all()->keyBy('id');
            $customers = Customer::all()->keyBy('id');

            return DataTables::eloquent($query)
                ->addColumn('no', function () {
                    return ''; // Diisi otomatis oleh meta render JS DataTables
                })
                ->addColumn('waktu', function ($b) {
                    $date = $b->created_at ? $b->created_at->format('d M Y') : '-';
                    $time = $b->created_at ? $b->created_at->format('H:i:s') . ' WIB' : '-';
                    return '<div class="font-mono text-xs text-slate-800 font-semibold">' . e($date) . '</div><div class="font-mono text-[11px] text-slate-400">' . e($time) . '</div>';
                })
                ->addColumn('kategori', function ($b) {
                    $badgeClass = match ($b->type) {
                        'maintenance' => 'badge-maintenance',
                        'announcement' => 'badge-announcement',
                        'promo' => 'badge-promo',
                        'billing' => 'badge-billing',
                        default => 'badge-general',
                    };
                    $typeLabel = match ($b->type) {
                        'maintenance' => 'Pemeliharaan',
                        'announcement' => 'Pengumuman',
                        'promo' => 'Promo',
                        'billing' => 'Tagihan',
                        default => 'Umum',
                    };
                    $typeIcon = match ($b->type) {
                        'maintenance' => 'fa-screwdriver-wrench',
                        'announcement' => 'fa-bullhorn',
                        'promo' => 'fa-gift',
                        'billing' => 'fa-file-invoice-dollar',
                        default => 'fa-bell',
                    };
                    return '<span class="badge-type ' . $badgeClass . '"><i class="fa-solid ' . $typeIcon . '"></i> ' . $typeLabel . '</span>';
                })
                ->addColumn('pesan', function ($b) {
                    $title = '<div class="font-bold text-slate-800 text-xs mb-0.5">' . e($b->title) . '</div>';
                    $body = '<div class="text-slate-500 text-[11px] line-clamp-1 leading-snug" title="' . e($b->body) . '">' . e(\Illuminate\Support\Str::limit($b->body, 90)) . '</div>';
                    return $title . $body;
                })
                ->addColumn('target', function ($b) use ($pakets, $customers) {
                    if ($b->target_type === 'package') {
                        $pkg = $pakets->get($b->target_id);
                        $label = 'Paket: ' . ($pkg ? $pkg->nama_paket : '#' . $b->target_id);
                        $icon = 'fa-box text-indigo-500';
                    } elseif ($b->target_type === 'customer') {
                        $cust = $customers->get($b->target_id);
                        $label = 'Pelanggan: ' . ($cust ? $cust->nama_lengkap : '#' . $b->target_id);
                        $icon = 'fa-user text-amber-500';
                    } else {
                        $label = 'Semua Pelanggan';
                        $icon = 'fa-globe text-sky-500';
                    }
                    return '<span class="inline-flex items-center gap-1.5 text-xs text-slate-700 font-medium"><i class="fa-solid ' . $icon . ' text-[11px]"></i> ' . e($label) . '</span>';
                })
                ->addColumn('status_fcm', function ($b) {
                    return '<div class="inline-flex items-center gap-1.5 justify-center"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[11px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100" title="Push FCM Berhasil Dikirim"><i class="fa-solid fa-paper-plane text-[9px]"></i> ' . $b->total_sent . '</span><span class="text-slate-300">/</span><span class="text-slate-600 text-[11px] font-semibold" title="Total Target">' . $b->total_targeted . '</span></div>';
                })
                ->addColumn('creator', function ($b) {
                    return '<span class="text-xs text-slate-600 font-medium whitespace-nowrap">' . e($b->creator->name ?? 'Sistem') . '</span>';
                })
                ->addColumn('opsi', function ($b) use ($pakets, $customers) {
                    $targetLabel = match ($b->target_type) {
                        'package' => 'Paket: ' . ($pakets->get($b->target_id)->nama_paket ?? '#' . $b->target_id),
                        'customer' => 'Pelanggan: ' . ($customers->get($b->target_id)->nama_lengkap ?? '#' . $b->target_id),
                        default => 'Semua Pelanggan',
                    };

                    $typeLabel = match ($b->type) {
                        'maintenance' => 'Pemeliharaan',
                        'announcement' => 'Pengumuman',
                        'promo' => 'Promo',
                        'billing' => 'Tagihan',
                        default => 'Umum',
                    };

                    $dataJson = htmlspecialchars(json_encode([
                        'id' => $b->id,
                        'title' => $b->title,
                        'body' => $b->body,
                        'type' => $b->type,
                        'type_label' => $typeLabel,
                        'target_label' => $targetLabel,
                        'total_targeted' => $b->total_targeted,
                        'total_sent' => $b->total_sent,
                        'total_failed' => $b->total_failed,
                        'created_at' => $b->created_at ? $b->created_at->format('d/m/Y H:i:s') : '-',
                        'creator' => $b->creator->name ?? 'Sistem',
                    ]), ENT_QUOTES, 'UTF-8');

                    $btnView = '<button type="button" class="btn-cancel !p-1 !h-7 !w-7 text-xs text-slate-500 hover:text-sky-600 hover:bg-sky-50 shadow-none border-slate-200" title="Lihat Detail Pesan" onclick=\'showDetailModal(' . $dataJson . ')\'><i class="fa-solid fa-eye"></i></button>';
                    $btnDel = '<button type="button" class="btn-cancel !p-1 !h-7 !w-7 text-xs text-slate-400 hover:text-rose-600 hover:bg-rose-50 shadow-none border-slate-200" title="Hapus Riwayat" onclick="confirmDeleteBroadcast(' . $b->id . ')"><i class="fa-solid fa-trash-can"></i></button>';

                    return '<div class="flex items-center justify-center gap-1.5">' . $btnView . $btnDel . '</div>';
                })
                ->rawColumns(['waktu', 'kategori', 'pesan', 'target', 'status_fcm', 'creator', 'opsi'])
                ->order(function ($q) {
                    $q->orderBy('broadcast_notifications.id', 'desc');
                })
                ->make(true);
        }

        // Statistik ringkasan
        $stats = [
            'total_broadcasts' => BroadcastNotification::count(),
            'total_sent'       => (int) BroadcastNotification::sum('total_sent'),
            'total_targeted'   => (int) BroadcastNotification::sum('total_targeted'),
            'fcm_registered'   => Customer::whereNotNull('fcm_token')->where('fcm_token', '!=', '')->count(),
            'total_customers'  => Customer::count(),
        ];

        $pakets = Paket::orderBy('nama_paket')->get(['id', 'nama_paket', 'rate_limit', 'harga']);
        $customers = Customer::orderBy('nama_lengkap')->get(['id', 'id_pelanggan', 'nama_lengkap', 'fcm_token']);

        return view('notifications.broadcast', compact('stats', 'pakets', 'customers'));
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
    public function destroy(Request $request, $broadcast = null)
    {
        $id = null;
        if ($broadcast instanceof BroadcastNotification) {
            $id = $broadcast->id;
        } elseif (!empty($broadcast) && is_numeric($broadcast)) {
            $id = (int) $broadcast;
        } elseif ($request->filled('id')) {
            $id = (int) $request->input('id');
        } elseif ($request->route('broadcast')) {
            $r = $request->route('broadcast');
            $id = $r instanceof BroadcastNotification ? $r->id : (int) $r;
        }

        if ($id) {
            BroadcastNotification::where('id', $id)->delete();
        }

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Riwayat pemberitahuan berhasil dihapus.'
            ]);
        }
        return redirect()->route('broadcasts.index')->with('success', 'Riwayat pemberitahuan berhasil dihapus.');
    }
}
