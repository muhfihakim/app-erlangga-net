<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAuthenticated
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    // public function handle(Request $request, Closure $next): Response
    // {
    //     if (!Auth::check()) {
    //         return redirect('/login');
    //     }

    //     return $next($request);
    // }

    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $user = Auth::user();

        // Jika user memiliki 2FA dan belum terverifikasi di session
        if ($user->google2fa_secret && !$request->session()->get('2fa_verified')) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            // Simpan ID user untuk login kembali setelah verifikasi
            session(['2fa:user:id' => $user->id]);
            return redirect()->route('2fa.verify');
        }

        return $next($request);
    }
}
