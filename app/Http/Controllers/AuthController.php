<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // Tampilkan Halaman Login
    public function showLogin()
    {
        return view('auth.login');
    }

    // Proses Login
    public function login(Request $request)
    {
        // Honeypot Check (Anti-Bot)
        if ($request->filled('website')) {
            \App\Models\AuditLog::log('BOT_DETECTED', 'Login Page', 'Honeypot triggered during login attempt.');
            return back()->withErrors(['email' => 'System detected unusual activity.']);
        }

        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            \App\Models\AuditLog::log('LOGIN', 'User Login', 'User logged in successfully.');
            return redirect()->intended('/');
        }

        return back()->withErrors([
            'email' => 'Email atau password salah Boss!',
        ]);
    }

    // Proses Logout
    public function logout(Request $request)
    {
        if(Auth::check()){
             \App\Models\AuditLog::log('LOGOUT', 'User Logout', 'User logged out.');
        }
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}
