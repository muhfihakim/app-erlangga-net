<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Dismantle;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DismantleController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = Dismantle::with(['customer', 'teknisiEksekutor']);

        if ($user->jabatan === 'Teknisi') {
            $query->where('id_teknisi_delegasi', $user->id);
        }

        return response()->json($query->latest()->paginate(20));
    }

    public function complete(Request $request, $id)
    {
        $request->validate([
            'keterangan' => 'required',
            'foto_perangkat' => 'required|image',
            'foto_rumah' => 'required|image',
        ]);

        $d = Dismantle::findOrFail($id);
        $customer = Customer::findOrFail($d->customer_id);

        $d->update([
            'id_teknisi_eksekutor' => auth()->id(),
            'keterangan' => $request->keterangan,
            'foto_perangkat' => $request->file('foto_perangkat')->store('dismantles/devices', 'public'),
            'foto_rumah' => $request->file('foto_rumah')->store('dismantles/houses', 'public'),
            'completed_at' => now(),
            'status' => 'Selesai'
        ]);

        // Customer is soft deleted after dismantle
        $customer->delete();

        return response()->json(['message' => 'Laporan dismantle berhasil disimpan dan pelanggan dinonaktifkan']);
    }
}
