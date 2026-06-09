<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AccountController extends Controller
{
    public function index()
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $users = User::all();
        $stats = [
            'total_users' => User::count(),
            'total_admin' => User::where('jabatan', 'Administrator')->count(),
            'total_sales' => User::where('jabatan', 'Sales')->count(),
            'total_teknisi' => User::where('jabatan', 'Teknisi')->count(),
        ];

        return response()->json([
            'stats' => $stats,
            'data' => $users
        ]);
    }

    public function show($id)
    {
        if (!auth()->user()->isAdmin() && auth()->id() != $id) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $user = User::findOrFail($id);
        return response()->json($user);
    }

    public function store(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'nik' => 'nullable|string|max:255',
            'alamat' => 'nullable|string',
            'jenis_kelamin' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'jabatan' => 'required|in:Administrator,Sales,Teknisi',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $user = User::create($validated);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity_type' => 'user_create',
            'description' => 'Menambahkan akun user: ' . $user->nama_lengkap,
            'logged_at' => now(),
        ]);

        return response()->json(['message' => 'Akun berhasil ditambahkan', 'data' => $user], 201);
    }

    public function update(Request $request, $id)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $user = User::findOrFail($id);

        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|nullable|string|min:8',
            'nik' => 'nullable|string|max:255',
            'alamat' => 'nullable|string',
            'jenis_kelamin' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'jabatan' => 'required|in:Administrator,Sales,Teknisi',
        ]);

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']);
        }

        $user->update($validated);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity_type' => 'user_update',
            'description' => 'Mengubah data akun user: ' . $user->nama_lengkap,
            'logged_at' => now(),
        ]);

        return response()->json(['message' => 'Akun berhasil diperbarui', 'data' => $user]);
    }

    public function destroy($id)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $user = User::findOrFail($id);
        $nama = $user->nama_lengkap;
        $user->delete();

        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity_type' => 'user_delete',
            'description' => 'Menghapus akun user: ' . $nama,
            'logged_at' => now(),
        ]);

        return response()->json(['message' => 'Akun berhasil dihapus']);
    }

    public function reset2FA($id)
    {
        if (!auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Forbidden'], 403);
        }

        $user = User::findOrFail($id);
        $user->google2fa_secret = null;
        $user->save();

        return response()->json(['message' => '2FA berhasil direset']);
    }
}
