<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FA\Google2FA;
use BaconQrCode\Writer;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;


class AccountController extends Controller
{
    public function index()
    {

        if (!auth()->user()->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        $users = User::all(); // Ini otomatis hanya mengambil yang belum didelete (soft)
        
        // --- STATISTIK ---
        $totalUsers = User::count();
        $totalAdmin = User::whereIn('jabatan', ['Administrator', 'Web Developer'])->count();
        $totalSales = User::where('jabatan', 'Sales')->count();
        $totalTeknisi = User::where('jabatan', 'Teknisi')->count();

        return view('accounts.index', compact(
            'users',
            'totalUsers',
            'totalAdmin',
            'totalSales',
            'totalTeknisi'
        ));
    }

    public function store(Request $request)
    {

        if (!auth()->user()->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'nik' => 'nullable|string|max:255',
            'alamat' => 'nullable|string',
            'jenis_kelamin' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'jabatan' => 'required|in:Administrator,Sales,Teknisi,Web Developer',
        ]);

        User::create([
            'nama_lengkap' => $request->nama_lengkap,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'nik' => $request->nik,
            'alamat' => $request->alamat,
            'jenis_kelamin' => $request->jenis_kelamin,
            'tanggal_lahir' => $request->tanggal_lahir,
            'jabatan' => $request->jabatan,
        ]);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity_type' => 'user_create',
            'description' => 'Menambahkan akun user: ' . $request->nama_lengkap,
            'logged_at' => now(),
        ]);

        return redirect()->route('accounts.index')->with('success', 'Akun berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        $validatedData = $request->validate([
            'nama_lengkap' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'sometimes|nullable|string|min:8',
            'nik' => 'nullable|string|max:255',
            'alamat' => 'nullable|string',
            'jenis_kelamin' => 'nullable|string|max:255',
            'tanggal_lahir' => 'nullable|date',
            'jabatan' => 'required|in:Administrator,Sales,Teknisi,Web Developer',
        ]);

        $dataToUpdate = $validatedData;

        if (!empty($request->input('password'))) {

            $dataToUpdate['password'] = Hash::make($request->input('password'));
        } else {

            unset($dataToUpdate['password']);
        }

        $user->update($dataToUpdate);

        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity_type' => 'user_update',
            'description' => 'Mengubah data akun user: ' . $user->nama_lengkap,
            'logged_at' => now(),
        ]);

        return redirect()->route('accounts.index')->with('success', 'Akun berhasil diperbarui.');
    }

    public function reset2FA(Request $request, User $user)
    {
        // Pastikan hanya Administrator yang bisa melakukan ini
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        // Reset secret Google Authenticator
        $user->google2fa_secret = null;
        $user->save();

        // Kembalikan ke halaman sebelumnya dengan pesan sukses (Flash Session)
        // Pesan ini akan ditangkap oleh skrip SweetAlert di Blade Anda
        return redirect()->back()->with('success', '2FA untuk user ' . $user->nama_lengkap . ' berhasil direset.');
    }

    public function show2FA(User $user)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        if (!$user->google2fa_secret) {
            return response()->json(['error' => 'User belum mengaktifkan 2FA'], 404);
        }

        $google2fa = new Google2FA();

        $qrText = $google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email,
            $user->google2fa_secret
        );

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($qrText);

        return response()->json([
            'secret' => $user->google2fa_secret,
            'qr' => $qrCodeSvg,
        ]);
    }

    public function destroy(User $user)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        $nama = $user->nama_lengkap;

        // Hapus akun
        $user->delete();

        // Catat log
        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity_type' => 'user_delete',
            'description' => 'Menghapus akun user: ' . $nama,
            'logged_at' => now(),
        ]);

        return redirect()->route('accounts.index')->with('success', 'Akun berhasil dihapus.');
    }

    public function toggleStatus(User $user)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Akses ditolak.');
        }

        if (auth()->id() === $user->id) {
            return redirect()->back()->with('error', 'Anda tidak dapat menonaktifkan akun Anda sendiri.');
        }

        $user->is_active = !$user->is_active;
        $user->save();

        $statusText = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        ActivityLog::create([
            'user_id' => auth()->id(),
            'activity_type' => 'user_status_update',
            'description' => 'Akun ' . $user->nama_lengkap . ' ' . $statusText,
            'logged_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Akun berhasil ' . $statusText . '.');
    }
}
