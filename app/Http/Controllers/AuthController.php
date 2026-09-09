<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Show the login form for web.
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $credentials = $request->only('email', 'password');

        if (Auth::attempt($credentials, true)) {
            $user = Auth::user();

            if (!$user->is_active) {
                Auth::logout();
                return back()->withErrors([
                    'email' => 'Akun Anda telah dinonaktifkan. Silakan hubungi Administrator.',
                ])->withInput($request->only('email'));
            }

            // Log activity
            \App\Models\ActivityLog::create([
                'user_id' => $user->id,
                'activity_type' => 'login',
                'description' => 'User login via Web Dashboard',
                'logged_at' => now(),
            ]);

            $token = $user->createToken('webview')->plainTextToken;
            $request->session()->regenerate();
            $request->session()->put('api_token', $token);
            return redirect()->route('dashboards.index');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah.',
        ])->withInput($request->only('email'));
    }

    public function logout(Request $request)
    {
        $user = Auth::user();

        if ($user) {
            \App\Models\ActivityLog::create([
                'user_id' => $user->id,
                'activity_type' => 'logout',
                'description' => 'User logout from Web Dashboard',
                'logged_at' => now(),
            ]);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function magicLogin(Request $request)
    {
        $token = $request->bearerToken();
        if (!$token) return response()->json(['error' => 'No token'], 401);
        
        $tokenRecord = \Laravel\Sanctum\PersonalAccessToken::findToken($token);
        if (!$tokenRecord || !$tokenRecord->tokenable) return response()->json(['error' => 'Invalid token'], 401);
        
        Auth::login($tokenRecord->tokenable, true);
        return response()->json(['success' => true]);
    }
}
