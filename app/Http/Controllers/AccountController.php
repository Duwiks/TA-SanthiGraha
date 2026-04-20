<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AccountController extends Controller
{
    /**
     * Menampilkan halaman pengaturan akun.
     */
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            return view('admin.account.index', compact('user'));
        }

        return view('pegawai.account.index', compact('user'));
    }

    /**
     * Update password akun.
     */
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:6|confirmed',
        ], [
            'current_password.required' => 'Password lama wajib diisi.',
            'new_password.required' => 'Password baru wajib diisi.',
            'new_password.min' => 'Password baru minimal 6 karakter.',
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $user = Auth::user();

        // Verifikasi password lama
        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password lama tidak sesuai.']);
        }

        // Update password
        $user->password = bcrypt($request->new_password);
        $user->save();

        return back()->with('success', 'Password berhasil diubah!');
    }
}
