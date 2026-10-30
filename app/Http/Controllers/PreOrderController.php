<?php

namespace App\Http\Controllers;

use App\Models\PreOrder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Models\ActivityLog;

class PreOrderController extends Controller
{

    public function index(Request $request)
    {
        if (auth()->check()) {
            $user = auth()->user();
            
            $filterStatus = $request->input('status', 'belum_pppoe');
            if (empty($filterStatus)) {
                $filterStatus = 'belum_pppoe';
            }
            $filterSales = $request->input('id_sales', '');

            // 1. Ambil ID PreOrder yang sudah terpasang (menjadi pelanggan di tabel customers, baik aktif maupun non-aktif/soft-deleted)
            $installedPOIds = \App\Models\Customer::withTrashed()
                ->whereNotNull('pre_order_id')
                ->pluck('pre_order_id')
                ->toArray();

            // 2. Query PreOrder yang murni PO (belum terpasang)
            $query = PreOrder::whereNotIn('id', $installedPOIds)
                ->with(['pppoe', 'paket', 'sales']);

            if ($user->isAdmin()) {
                if ($filterSales) {
                    $query->where('id_sales', $filterSales);
                }
            } elseif ($user->jabatan === 'Teknisi') {
                // Teknisi bisa melihat semua PO yang belum terpasang
            } else {
                $query->where('id_sales', $user->id);
            }

            // Apply Status Filters: Menampilkan berdasarkan status PPPoE
            if ($filterStatus === 'sudah_pppoe') {
                $query->whereHas('pppoe')->where('is_unspek', false);
            } elseif ($filterStatus === 'unspek') {
                $query->where('is_unspek', true);
            } elseif ($filterStatus === 'semua') {
                // Tampilkan semua PO yang belum terpasang
            } else {
                // Default: hanya tampilkan yang belum dibuatkan PPPoE-nya
                $filterStatus = 'belum_pppoe';
                $query->whereDoesntHave('pppoe')->where('is_unspek', false);
            }

            // 3. Sorting: Belum ada PPPoE & bukan unspek di atas, lalu ID terbaru
            if ($filterStatus === 'semua') {
                $preOrders = $query->orderBy('is_unspek', 'asc')
                    ->orderByRaw('CASE WHEN EXISTS (SELECT 1 FROM pppoes WHERE pppoes.pre_order_id = pre_orders.id) THEN 1 ELSE 0 END ASC')
                    ->orderBy('id', 'desc')
                    ->get();
            } else {
                $preOrders = $query->orderBy('is_unspek', 'asc')
                    ->orderBy('id', 'desc')
                    ->get();
            }

            $teknisiUsers = User::where('jabatan', 'Teknisi')->orderBy('nama_lengkap')->get();
            $salesUsers = User::where('jabatan', 'Sales')->orderBy('nama_lengkap')->get();

            if ($request->ajax()) {
                return \Yajra\DataTables\Facades\DataTables::of($preOrders)
                    ->addColumn('no', function ($po) {
                        return ''; // Handled by DataTables JS
                    })
                    ->editColumn('created_at', function($po) {
                        return '<div class="text-center font-mono text-xs text-slate-500">' . \Carbon\Carbon::parse($po->created_at)->format('d/m/Y') . '</div>';
                    })
                    ->addColumn('sales', function($po) {
                        return '<div class="text-left text-xs font-medium text-slate-700 whitespace-nowrap"><i class="fa-regular fa-user text-[11px] text-slate-400 mr-1.5"></i>' . htmlspecialchars($po->nama_sales ?: ($po->sales ? $po->sales->nama_lengkap : '-')) . '</div>';
                    })
                    ->addColumn('nama_lengkap', function($po) {
                        $html = '<div class="text-left font-bold text-slate-900 text-[13px] leading-snug">' . htmlspecialchars($po->nama_lengkap) . '</div>';
                        if ($po->no_whatsapp) {
                            $html .= '<div class="text-left text-[11px] font-mono text-slate-400 mt-0.5 flex items-center gap-1"><i class="fa-brands fa-whatsapp text-emerald-600 text-[11px]"></i>' . htmlspecialchars($po->no_whatsapp) . '</div>';
                        }
                        return $html;
                    })
                    ->addColumn('paket', function($po) {
                        return '<div class="text-center"><span class="badge" style="background: #f8fafc; color: #334155; border: 1px solid #e2e8f0; font-weight: 600;">' . htmlspecialchars($po->paket ? $po->paket->nama_paket : '-') . '</span></div>';
                    })
                    ->addColumn('status_opsi', function($po) {
                        return view('preorders.action_buttons', compact('po'))->render();
                    })
                    ->rawColumns(['created_at', 'sales', 'nama_lengkap', 'paket', 'status_opsi'])
                    ->make(true);
            }

            // --- STATISTIK ---
            $thisMonth = Carbon::now()->month;
            $thisYear = Carbon::now()->year;

            $statsQuery = PreOrder::whereNotIn('id', $installedPOIds);
            if ($user->jabatan === 'Sales') {
                $statsQuery->where('id_sales', $user->id);
            }

            $totalThisMonth = (clone $statsQuery)->whereMonth('created_at', $thisMonth)
                ->whereYear('created_at', $thisYear)
                ->count();

            $totalThisWeek = (clone $statsQuery)->whereBetween('created_at', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])->count();

            $totalBelumPppoe = (clone $statsQuery)->where('is_unspek', false)->whereDoesntHave('pppoe')->count();
            $totalSudahPppoe = (clone $statsQuery)->where('is_unspek', false)->whereHas('pppoe')->count();
            $totalUnspek = (clone $statsQuery)->where('is_unspek', true)->count();
            $totalPending = $totalBelumPppoe;

            $pakets = \App\Models\Paket::orderBy('harga', 'asc')->get();

            try {
                $routers = \App\Models\Nas::orderBy('shortname')->get();
            } catch (\Throwable $e) {
                $routers = collect();
            }

            return view('preorders.index', compact(
                'preOrders',
                'teknisiUsers',
                'salesUsers',
                'pakets',
                'routers',
                'totalThisMonth',
                'totalThisWeek',
                'totalBelumPppoe',
                'totalSudahPppoe',
                'totalPending',
                'totalUnspek',
                'filterStatus',
                'filterSales'
            ));
        }
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->jabatan !== 'Sales' && !$user->isAdmin()) {
            return redirect()->route('pre_orders.index')->with('error', 'Akses ditolak.');
        }

        try {
            $validatedData = $request->validate([
                'paket_id' => 'required|exists:pakets,id',
                'nama_lengkap' => 'required|string|max:255',
                'no_identitas' => 'required|string|max:255|unique:pre_orders,no_identitas',
                'no_whatsapp' => 'required|string|max:255',
                'alamat_email' => 'nullable|string|email|max:255',
                'alamat_lengkap' => 'required|string',
                'titik_koordinat' => 'required|string',
                'foto_ktp' => 'nullable|image|max:20480',
                'foto_rumah' => 'nullable|image|max:20480',
            ], [
                'no_identitas.unique' => 'No. Identitas ini sudah terdaftar.',
                'alamat_email.email' => 'Format alamat email tidak valid.',
            ]);

            if ($request->hasFile('foto_ktp')) {
                $fotoKtpPath = $request->file('foto_ktp')->store('pre_orders/ktp');
                $validatedData['foto_ktp'] = $fotoKtpPath;
            }

            if ($request->hasFile('foto_rumah')) {
                $fotoRumahPath = $request->file('foto_rumah')->store('pre_orders/rumah');
                $validatedData['foto_rumah'] = $fotoRumahPath;
            }

            PreOrder::create(array_merge($validatedData, [
                'id_sales' => $user->id,
                'nama_sales' => $user->nama_lengkap,
            ]));

            ActivityLog::create([
                'user_id' => $user->id,
                'activity_type' => 'preorder_create',
                'description' => 'Menambahkan PreOrder for ' . $validatedData['nama_lengkap'],
                'logged_at' => now(),
            ]);

            return redirect()->route('pre_orders.index')->with('success', 'Pre-order berhasil ditambahkan!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            $errorMessages = implode('<br>', $e->validator->errors()->all());
            return redirect()->back()->withInput()->withErrors($e->validator)->with('error', $errorMessages);
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage());
        }
    }


    public function update(Request $request, PreOrder $preOrder)
    {
        if (Auth::user()->jabatan !== 'Sales' && !Auth::user()->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        try {
            $validatedData = $request->validate([
                'paket_id' => 'sometimes|exists:pakets,id',
                'nama_lengkap' => 'sometimes|string|max:255',
                'no_identitas' => 'sometimes|string|max:255',
                'no_whatsapp' => 'sometimes|string|max:255',
                'alamat_email' => 'sometimes|nullable|string|email|max:255',
                'alamat_lengkap' => 'sometimes|string',
                'titik_koordinat' => 'sometimes|string',
                'foto_ktp' => 'sometimes|image|max:20480',
                'foto_rumah' => 'sometimes|image|max:20480',
            ], [
                'alamat_email.email' => 'Format alamat email tidak valid.',
            ]);

            // Pisahkan data file dari data teks
            $updateData = collect($validatedData)->except(['foto_ktp', 'foto_rumah'])->toArray();
            $preOrder->fill($updateData);

            if ($request->hasFile('foto_ktp')) {
                if ($preOrder->getOriginal('foto_ktp')) {
                    Storage::delete($preOrder->getOriginal('foto_ktp'));
                }
                $path = $request->file('foto_ktp')->store('pre_orders/ktp');
                $preOrder->foto_ktp = $path;
            }

            if ($request->hasFile('foto_rumah')) {
                if ($preOrder->getOriginal('foto_rumah')) {
                    Storage::delete($preOrder->getOriginal('foto_rumah'));
                }
                $path = $request->file('foto_rumah')->store('pre_orders/rumah');
                $preOrder->foto_rumah = $path;
            }

            if ($preOrder->isDirty()) {
                $preOrder->save();
                ActivityLog::create([
                    'user_id' => Auth::id(),
                    'activity_type' => 'preorder_update',
                    'description' => 'Mengubah PreOrder: ' . $preOrder->nama_lengkap,
                    'logged_at' => now(),
                ]);
                return redirect()->route('pre_orders.index')->with('success', 'Data pre-order berhasil diperbarui.');
            }

            return redirect()->route('pre_orders.index')->with('success', 'Tidak ada perubahan pada data pre-order.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()->withErrors($e->validator)->withInput()->with('error', 'Gagal update: ' . implode(', ', $e->validator->errors()->all()));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }


    public function destroy(PreOrder $preOrder)
    {
        if (Auth::user()->jabatan !== 'Sales' && !Auth::user()->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        if ($preOrder->foto_ktp) Storage::delete($preOrder->foto_ktp);
        if ($preOrder->foto_rumah) Storage::delete($preOrder->foto_rumah);

        $preOrder->delete();

        ActivityLog::create([
            'user_id' => Auth::id(),
            'activity_type' => 'preorder_delete',
            'description' => 'Menghapus PreOrder: ' . $preOrder->nama_lengkap,
            'logged_at' => now(),
        ]);

        return redirect()->route('pre_orders.index')->with('success', 'Data pre-order berhasil dihapus.');
    }

    public function unspek(Request $request, PreOrder $preOrder)
    {
        if (!in_array(auth()->user()->jabatan, ['Administrator', 'Teknisi', 'Web Developer'])) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'keterangan_unspek' => 'required|string|max:1000',
        ]);

        $preOrder->update([
            'is_unspek' => true,
            'is_unspek_approved' => false,
            'keterangan_unspek' => $request->keterangan_unspek,
        ]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity_type' => 'preorder_unspek',
            'description' => 'Unspek PO "' . $preOrder->nama_lengkap . '": ' . $request->keterangan_unspek,
            'logged_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Tugas Aktivasi berhasil ditangguhkan/Unspek.');
    }

    public function approveUnspek(PreOrder $preOrder)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && ($user->jabatan !== 'Sales' || $preOrder->id_sales !== $user->id)) {
            abort(403, 'Akses ditolak.');
        }

        $preOrder->update([
            'is_unspek_approved' => true,
        ]);

        ActivityLog::create([
            'user_id' => $user->id,
            'activity_type' => 'preorder_unspek_approve',
            'description' => 'Approve Unspek PO "' . $preOrder->nama_lengkap . '"',
            'logged_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Unspek / Penangguhan PO berhasil disetujui.');
    }

    public function reopenUnspek(PreOrder $preOrder)
    {
        $user = auth()->user();
        if (!$user->isAdmin() && ($user->jabatan !== 'Sales' || $preOrder->id_sales !== $user->id)) {
            abort(403, 'Akses ditolak.');
        }

        $preOrder->update([
            'is_unspek' => false,
            'is_unspek_approved' => false,
            'keterangan_unspek' => null,
        ]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity_type' => 'preorder_unspek_reopen',
            'description' => 'Membuka kembali (Re-Open) PO Unspek "' . $preOrder->nama_lengkap . '"',
            'logged_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Tugas berhasil dibuka kembali menjadi aktif.');
    }
}
