<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\PreOrder;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Customer::with(['sales', 'teknisiUtama']);

        if ($user->jabatan === 'Sales') {
            $query->where('id_sales', $user->id);
        }

        $customers = $query->latest()->paginate(20);

        // --- MAPPING STATUS ONLINE/OFFLINE ---
        $pppoeData = Cache::get('mikrotik_pppoe_active');
        $activeUsernames = [];
        if ($pppoeData && isset($pppoeData['success']) && $pppoeData['success']) {
            $activeUsernames = collect($pppoeData['data'])->pluck('name')->toArray();
        }

        $customers->getCollection()->transform(function ($customer) use ($activeUsernames) {
            $customer->is_online = in_array($customer->pppoe_username, $activeUsernames);
            return $customer;
        });

        // --- STATS (Mirror activeList logic) ---
        $statsQuery = Customer::query();
        if ($user->jabatan === 'Sales') $statsQuery->where('id_sales', $user->id);

        $stats = [
            'total_active' => (clone $statsQuery)->count(),
            'new_this_month' => (clone $statsQuery)->whereMonth('created_at', Carbon::now()->month)->count(),
            'new_this_week' => (clone $statsQuery)->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()])->count(),
            'total_archived' => (clone $statsQuery)->onlyTrashed()->count(),
        ];

        return response()->json([
            'stats' => $stats,
            'data' => $customers
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'pre_order_id' => 'required|exists:pre_orders,id|unique:customers,pre_order_id',
            'id_teknisi' => 'required|exists:users,id',
            'pppoe_username' => 'required|string|max:255|unique:customers,pppoe_username',
            'pppoe_password' => 'required|string|min:1',
            'id_pelanggan' => 'required|string|max:255|unique:customers,id_pelanggan',
            'jenis_modem' => 'required|in:EPON,GPON',
            'sn_perangkat' => 'required|string|max:255',
            'pembayaran' => 'required|in:Cash,Aplikasi',
            'partner_ids' => 'nullable|array',
            'partner_ids.*' => 'exists:users,id',
            'keterangan' => 'nullable|string',
            'foto_petugas' => 'required|image|max:20480',
            'foto_terpasang' => 'required|image|max:20480',
            'foto_speedtest' => 'required|image|max:20480',
        ]);

        $po = PreOrder::findOrFail($request->pre_order_id);
        $partnerNames = "";
        if ($request->has('partner_ids') && !empty($request->partner_ids)) {
            $partnerNames = User::whereIn('id', $request->partner_ids)->pluck('nama_lengkap')->implode(', ');
        }

        $customer = Customer::create([
            'pre_order_id' => $po->id,
            'id_sales' => $po->id_sales,
            'id_teknisi' => $request->id_teknisi,
            'nama_lengkap' => $po->nama_lengkap,
            'alamat_lengkap' => $po->alamat_lengkap,
            'no_whatsapp' => $po->no_whatsapp,
            'paket_internet' => $po->paket_internet,
            'id_pelanggan' => $request->id_pelanggan,
            'pppoe_username' => $request->pppoe_username,
            'pppoe_password' => $request->pppoe_password,
            'jenis_modem' => $request->jenis_modem,
            'sn_perangkat' => $request->sn_perangkat,
            'pembayaran' => $request->pembayaran,
            'partner' => $partnerNames,
            'partner_ids' => $request->partner_ids,
            'keterangan' => $request->keterangan,
            'foto_petugas' => $request->file('foto_petugas')->store('customers/foto_petugas'),
            'foto_terpasang' => $request->file('foto_terpasang')->store('customers/foto_terpasang'),
            'foto_speedtest' => $request->file('foto_speedtest')->store('customers/foto_speedtest'),
        ]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity_type' => 'aktivasi_pelanggan',
            'description' => 'Mengaktivasi pelanggan: ' . $po->nama_lengkap,
            'logged_at' => now(),
        ]);

        return response()->json(['message' => 'Aktivasi pelanggan berhasil', 'data' => $customer], 201);
    }

    public function isolir(Request $request)
    {
        $user = $request->user();
        $pppoeData = Cache::get('mikrotik_pppoe_active');
        
        if (!$pppoeData || !isset($pppoeData['success']) || !$pppoeData['success']) {
            return response()->json(['data' => []]);
        }

        $isolirUsernames = collect($pppoeData['data'])
            ->filter(fn($item) => str_starts_with($item['address'] ?? '', '172.30.'))
            ->pluck('name')->toArray();

        $query = Customer::with('sales')->whereIn('pppoe_username', $isolirUsernames);
        
        if ($user->jabatan === 'Sales') {
            $query->where('id_sales', $user->id);
        }

        return response()->json(['data' => $query->get()]);
    }

    public function show($id)
    {
        $customer = Customer::with(['sales', 'preOrder', 'teknisiUtama'])->findOrFail($id);
        return response()->json($customer);
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();
        
        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity_type' => 'customer_delete',
            'description' => 'Menghapus (soft delete) pelanggan: ' . $customer->nama_lengkap,
            'logged_at' => now(),
        ]);

        return response()->json(['message' => 'Pelanggan berhasil dinonaktifkan']);
    }

    public function restore($id)
    {
        $customer = Customer::onlyTrashed()->findOrFail($id);
        $customer->restore();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity_type' => 'customer_restore',
            'description' => 'Mengaktifkan kembali pelanggan: ' . $customer->nama_lengkap,
            'logged_at' => now(),
        ]);

        return response()->json(['message' => 'Pelanggan berhasil diaktifkan kembali']);
    }
}
