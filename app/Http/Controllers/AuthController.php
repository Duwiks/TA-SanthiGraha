<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

class AuthController extends Controller
{
    // Tampilkan form registrasi pegawai
    public function showRegister()
    {
        return view('auth.register'); // Ganti view sesuai folder blade Anda jika berbeda
    }

    // Proses Register (Hanya Pegawai)
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:users',
            'password' => 'required|string|min:6', // Tambahkan 'confirmed' jika form pakai re-type password
            'phone' => 'nullable|string|max:20',
        ]);

        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'password' => bcrypt($request->password), // Eloquent di Laravel 11 sudah auto-cast 'hashed', tapi gapapa dipastikan
            'role' => 'pegawai', // Force role menjadi pegawai saat register biasa
            'phone' => $request->phone,
        ]);

        return redirect()->route('login')->with('success', 'Registrasi pegawai berhasil! Silakan login.');
    }

    // Tampilkan form login
    public function showLogin()
    {
        return view('auth.login'); // Ganti view sesuai folder blade Anda
    }

    // Proses login
    public function login(Request $request)
    {
        $credentials = $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            
            return redirect()->intended('/')->with('success', 'Login berhasil!');
        }

        return back()->withErrors([
            'username' => 'Username atau password salah',
        ])->onlyInput('username'); 
    }

    // Proses logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Logout berhasil');
    }
}
