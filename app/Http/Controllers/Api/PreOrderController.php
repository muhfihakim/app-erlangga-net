<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PreOrder;
use App\Models\Customer;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PreOrderController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $filterStatus = $request->input('status', '');
        $filterSales = $request->input('id_sales', '');

        $customerRecords = Customer::withTrashed()->get(['pre_order_id', 'deleted_at']);
        $activePOIds = $customerRecords->whereNull('deleted_at')->pluck('pre_order_id')->toArray();
        $unsubscribedPOIds = $customerRecords->whereNotNull('deleted_at')->pluck('pre_order_id')->toArray();

        $query = PreOrder::with(['sales', 'teknisiDelegasi']);

        if ($user->jabatan === 'Administrator') {
            if ($filterSales) $query->where('id_sales', $filterSales);
        } elseif ($user->jabatan === 'Teknisi') {
            $query->where(fn($q) => $q->where('id_teknisi_delegasi', $user->id)->orWhereNull('id_teknisi_delegasi'));
        } else {
            $query->where('id_sales', $user->id);
        }

        if ($filterStatus === 'pending') {
            $query->whereNotIn('id', array_merge($activePOIds, $unsubscribedPOIds))->where('is_unspek', false);
        } elseif ($filterStatus === 'aktif') {
            $query->whereIn('id', $activePOIds);
        } elseif ($filterStatus === 'berhenti') {
            $query->whereIn('id', $unsubscribedPOIds);
        } elseif ($filterStatus === 'unspek') {
            $query->where('is_unspek', true);
        }

        $results = $query->latest()->paginate(20);

        $results->getCollection()->transform(function ($po) use ($activePOIds, $unsubscribedPOIds) {
            $po->is_active = in_array($po->id, $activePOIds);
            $po->is_unsubscribed = in_array($po->id, $unsubscribedPOIds);
            $po->is_expired = false;
            $po->is_segera_expired = false;

            if ($po->delegated_at && !$po->is_active && !$po->is_unsubscribed) {
                $deadline = Carbon::parse($po->delegated_at)->addDays(2);
                if (Carbon::now()->greaterThan($deadline)) {
                    $po->is_expired = true;
                } elseif (Carbon::now()->diffInHours($deadline) <= 24) {
                    $po->is_segera_expired = true;
                }
            }
            return $po;
        });

        return response()->json($results);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'paket_internet' => 'required|string|max:255',
            'nama_lengkap' => 'required|string|max:255',
            'no_identitas' => 'required|string|max:255|unique:pre_orders,no_identitas',
            'no_whatsapp' => 'required|string|max:255',
            'alamat_email' => 'nullable|string|email|max:255',
            'alamat_lengkap' => 'required|string',
            'titik_koordinat' => 'required|string',
            'foto_ktp' => 'nullable|image|max:20480',
            'foto_rumah' => 'nullable|image|max:20480',
        ]);

        $validated['id_sales'] = auth()->id();
        
        if ($request->hasFile('foto_ktp')) {
            $validated['foto_ktp'] = $request->file('foto_ktp')->store('preorders/ktp', 'public');
        }
        if ($request->hasFile('foto_rumah')) {
            $validated['foto_rumah'] = $request->file('foto_rumah')->store('preorders/rumah', 'public');
        }

        $po = PreOrder::create($validated);

        return response()->json(['message' => 'Pre-Order berhasil dibuat', 'data' => $po], 201);
    }

    public function show($id)
    {
        $po = PreOrder::with(['sales', 'teknisiDelegasi'])->findOrFail($id);
        return response()->json($po);
    }

    public function delegate(Request $request, $id)
    {
        $request->validate(['id_teknisi' => 'required|exists:users,id']);
        $po = PreOrder::findOrFail($id);
        $po->update([
            'id_teknisi_delegasi' => $request->id_teknisi,
            'delegated_at' => now()
        ]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity_type' => 'preorder_delegate',
            'description' => 'Mendelegasikan PO "' . $po->nama_lengkap . '" ke teknisi.',
            'logged_at' => now(),
        ]);

        return response()->json(['message' => 'PO berhasil didelegasikan']);
    }

    public function resetDelegation($id)
    {
        $po = PreOrder::findOrFail($id);
        $po->update([
            'id_teknisi_delegasi' => null,
            'delegated_at' => null
        ]);

        return response()->json(['message' => 'Delegasi PO berhasil direset']);
    }

    public function unspek(Request $request, $id)
    {
        $request->validate(['keterangan_unspek' => 'required|string']);
        $po = PreOrder::findOrFail($id);
        $po->update([
            'is_unspek' => true,
            'keterangan_unspek' => $request->keterangan_unspek,
            'unspek_at' => now()
        ]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity_type' => 'preorder_unspek',
            'description' => 'Menandai PO "' . $po->nama_lengkap . '" sebagai Unspek.',
            'logged_at' => now(),
        ]);

        return response()->json(['message' => 'PO berhasil ditandai sebagai Unspek']);
    }

    public function approveUnspek($id)
    {
        $po = PreOrder::findOrFail($id);
        $po->update(['unspek_approved_at' => now()]);

        return response()->json(['message' => 'Unspek PO telah disetujui']);
    }

    public function reopen(Request $request, $id)
    {
        $po = PreOrder::findOrFail($id);
        $po->update([
            'is_unspek' => false,
            'keterangan_unspek' => null,
            'unspek_at' => null,
            'unspek_approved_at' => null
        ]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity_type' => 'preorder_reopen',
            'description' => 'Membuka kembali PO "' . $po->nama_lengkap . '".',
            'logged_at' => now(),
        ]);

        return response()->json(['message' => 'PO berhasil dibuka kembali']);
    }

    public function destroy($id)
    {
        $po = PreOrder::findOrFail($id);
        $po->delete();
        return response()->json(['message' => 'Pre-Order berhasil dihapus']);
    }
}
