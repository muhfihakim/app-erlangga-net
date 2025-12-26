<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /**
     * Tampilkan halaman login.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Simpan log login manual
            ActivityLog::create([
                'user_id' => $user->id,
                'activity_type' => 'login',
                'description' => 'User login',
                'logged_at' => Carbon::now(),
            ]);

            // Jika user belum punya 2FA secret → arahkan ke halaman setup
            if (is_null($user->google2fa_secret)) {
                return redirect()->route('2fa.setup');
            }

            // Jika sudah punya secret → logout dulu dan arahkan ke verifikasi kode
            session(['2fa:user:id' => $user->id]);
            Auth::logout();

            return redirect()->route('2fa.verify');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ]);
    }

    /**
     * Handle proses logout.
     */
    public function logout(Request $request)
    {
        $user = Auth::user();

        // Simpan log logout manual sebelum logout
        if ($user) {
            ActivityLog::create([
                'user_id' => $user->id,
                'activity_type' => 'logout',
                'description' => 'User logout',
                'logged_at' => now(),
            ]);
        }

        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->forget('2fa_verified');

        return redirect('/login');
    }
}
