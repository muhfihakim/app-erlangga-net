<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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

        if (Auth::attempt($credentials)) {
            $user = Auth::user();

            // Log activity
            \App\Models\ActivityLog::create([
                'user_id' => $user->id,
                'activity_type' => 'login',
                'description' => 'User login via ' . ($request->expectsJson() ? 'API' : 'Web'),
                'logged_at' => now(),
            ]);

            if ($request->expectsJson()) {
                $request->validate(['device_name' => 'required']);
                return response()->json([
                    'token' => $user->createToken($request->device_name)->plainTextToken,
                    'user' => $user
                ]);
            }

            $request->session()->regenerate();
            return redirect()->route('dashboards.index');
        }

        if ($request->expectsJson()) {
            throw ValidationException::withMessages([
                'email' => ['Kredensial yang diberikan salah.'],
            ]);
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
                'description' => 'User logout',
                'logged_at' => now(),
            ]);

            // Revoke current token if API
            if ($request->user()->currentAccessToken()) {
                $request->user()->currentAccessToken()->delete();
            }
        }

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Logged out successfully']);
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
