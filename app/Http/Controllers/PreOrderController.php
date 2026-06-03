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
            
            $filterStatus = $request->input('status', ''); // '', 'pending', 'aktif', 'berhenti', 'unspek'
            $filterSales = $request->input('id_sales', '');

            // 1. Ambil data mentah dari tabel Customer (Termasuk yang di-softdelete)
            $customerRecords = \App\Models\Customer::withTrashed()->get(['pre_order_id', 'deleted_at']);

            // Pisahkan ID berdasarkan statusnya
            $activePOIds = $customerRecords->whereNull('deleted_at')->pluck('pre_order_id')->toArray();
            $unsubscribedPOIds = $customerRecords->whereNotNull('deleted_at')->pluck('pre_order_id')->toArray();

            // 2. Query PreOrder
            $query = PreOrder::query();

            if ($user->jabatan === 'Administrator') {
                if ($filterSales) {
                    $query->where('id_sales', $filterSales);
                }
            } elseif ($user->jabatan === 'Teknisi') {
                // Teknisi bisa melihat semua PO yang belum aktif
            } else {
                $query->where('id_sales', $user->id);
            }

            // Apply Status Filters
            if ($filterStatus === 'pending') {
                $query->whereNotIn('id', array_merge($activePOIds, $unsubscribedPOIds))
                      ->where('is_unspek', false);
            } elseif ($filterStatus === 'aktif') {
                $query->whereIn('id', $activePOIds);
            } elseif ($filterStatus === 'berhenti') {
                $query->whereIn('id', $unsubscribedPOIds);
            } elseif ($filterStatus === 'unspek') {
                $query->where('is_unspek', true);
            }

            // 3. Sorting & Execution
            // Urutan: Pending (bukan aktif/berhenti/unspek) paling atas, lalu terbaru
            $preOrders = $query->get()->map(function ($po) use ($activePOIds, $unsubscribedPOIds) {
                $po->is_active = in_array($po->id, $activePOIds); 
                $po->is_unsubscribed = in_array($po->id, $unsubscribedPOIds);
                
                // Bobot untuk sorting: 0 = Pending, 1 = Lainnya
                $po->sort_weight = ($po->is_active || $po->is_unsubscribed || $po->is_unspek) ? 1 : 0;
                return $po;
            })->sortBy([
                ['sort_weight', 'asc'],
                ['id', 'desc']
            ]);

            $teknisiUsers = User::where('jabatan', 'Teknisi')->orderBy('nama_lengkap')->get();
            $salesUsers = User::where('jabatan', 'Sales')->orderBy('nama_lengkap')->get();

            // --- STATISTIK ---
            $thisMonth = Carbon::now()->month;
            $thisYear = Carbon::now()->year;

            $statsQuery = PreOrder::query();
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

            $allMyPOs = $statsQuery->get();
            $totalPending = $allMyPOs->filter(fn($po) => !in_array($po->id, $activePOIds) && !in_array($po->id, $unsubscribedPOIds))->count();

            return view('preorders.index', compact(
                'preOrders',
                'teknisiUsers',
                'salesUsers',
                'totalThisMonth',
                'totalThisWeek',
                'totalPending',
                'filterStatus',
                'filterSales'
            ));
        }
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->jabatan !== 'Sales' && $user->jabatan !== 'Administrator') {
            return redirect()->route('pre_orders.index')->with('error', 'Akses ditolak.');
        }

        try {
            $validatedData = $request->validate([
                'paket_internet' => 'required|string|max:255',
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
                $fotoKtpPath = $request->file('foto_ktp')->store('pre_orders/ktp', 'public');
                $validatedData['foto_ktp'] = $fotoKtpPath;
            }

            if ($request->hasFile('foto_rumah')) {
                $fotoRumahPath = $request->file('foto_rumah')->store('pre_orders/rumah', 'public');
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
        if (Auth::user()->jabatan !== 'Sales' && Auth::user()->jabatan !== 'Administrator') {
            abort(403, 'Akses ditolak.');
        }

        $validatedData = $request->validate([
            'paket_internet' => 'sometimes|string|max:255',
            'nama_lengkap' => 'sometimes|string|max:255',
            'no_identitas' => 'sometimes|string|max:255',
            'no_whatsapp' => 'sometimes|string|max:255',
            'alamat_email' => 'sometimes|string|email|max:255',
            'alamat_lengkap' => 'sometimes|string',
            'titik_koordinat' => 'sometimes|string',
            'foto_ktp' => 'sometimes|image|max:20480',
            'foto_rumah' => 'sometimes|image|max:20480',
        ]);

        $preOrder->fill($validatedData);

        if ($request->hasFile('foto_ktp')) {
            if (!empty($preOrder->getOriginal('foto_ktp')) && Storage::disk('public')->exists($preOrder->getOriginal('foto_ktp'))) {
                Storage::disk('public')->delete($preOrder->getOriginal('foto_ktp'));
            }
            $path = $request->file('foto_ktp')->store('pre_orders/ktp', 'public');
            $preOrder->foto_ktp = $path;
        }

        if ($request->hasFile('foto_rumah')) {
            if (!empty($preOrder->getOriginal('foto_rumah')) && Storage::disk('public')->exists($preOrder->getOriginal('foto_rumah'))) {
                Storage::disk('public')->delete($preOrder->getOriginal('foto_rumah'));
            }
            $path = $request->file('foto_rumah')->store('pre_orders/rumah', 'public');
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
    }


    public function destroy(PreOrder $preOrder)
    {
        if (Auth::user()->jabatan !== 'Sales' && Auth::user()->jabatan !== 'Administrator') {
            abort(403, 'Akses ditolak.');
        }

        if ($preOrder->foto_ktp) Storage::disk('public')->delete($preOrder->foto_ktp);
        if ($preOrder->foto_rumah) Storage::disk('public')->delete($preOrder->foto_rumah);

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
        if (!in_array(auth()->user()->jabatan, ['Administrator', 'Teknisi'])) {
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
        if ($user->jabatan !== 'Administrator' && ($user->jabatan !== 'Sales' || $preOrder->id_sales !== $user->id)) {
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
        if (auth()->user()->jabatan !== 'Administrator') {
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
