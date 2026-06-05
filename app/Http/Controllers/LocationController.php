<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    /**
     * Menyimpan atau memperbarui lokasi pengguna secara realtime.
     */
    public function update(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $user = Auth::user();

        // Cek jika pengguna bukan Administrator
        if ($user->isAdmin()) {
            return response()->json(['message' => 'Lokasi Administrator tidak dilacak.'], 403);
        }

        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        Location::updateOrCreate(
            ['user_id' => $user->id],
            [
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'nama_lengkap' => $user->nama_lengkap,
                'jabatan' => $user->jabatan,
            ]
        );

        return response()->json(['message' => 'Lokasi berhasil diperbarui.'], 200);
    }

    /**
     * Menampilkan halaman peta pelacakan.
     */
    public function showTrackingMap()
    {
        // Ambil hanya lokasi Teknisi dan Sales
        $locations = Location::whereIn('jabatan', ['Teknisi', 'Sales'])->get();
        // Ambil hanya user Teknisi dan Sales untuk dropdown
        $users = User::whereIn('jabatan', ['Teknisi', 'Sales'])->get();

        return view('map', compact('locations', 'users'));
    }

    /**
     * API: Mengambil semua lokasi yang ada (kecuali Administrator).
     */
    public function getAllLocations()
    {
        $locations = Location::whereIn('jabatan', ['Teknisi', 'Sales'])->get();
        return response()->json($locations);
    }

    /**
     * API: Mengambil lokasi spesifik berdasarkan ID pengguna (kecuali Administrator).
     */
    public function getUserLocation(User $user)
    {
        // Pastikan jabatan bukan Administrator sebelum mengambil lokasi
        if ($user->isAdmin()) {
            return response()->json([], 200);
        }

        $location = Location::where('user_id', $user->id)->get();
        return response()->json($location);
    }
}
