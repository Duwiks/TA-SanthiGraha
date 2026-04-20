<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PegawaiController extends Controller
{
    /**
     * Menampilkan daftar akun pegawai beserta status login.
     */
    public function index(Request $request)
    {
        $query = User::where('role', 'pegawai');

        // Search by name or username
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('username', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        $pegawai = $query->orderBy('name', 'asc')->paginate(10)->withQueryString();

        // Ambil user_id yang memiliki session aktif (login) dari tabel sessions
        $activeUserIds = DB::table('sessions')
            ->whereNotNull('user_id')
            ->where('last_activity', '>=', now()->subMinutes(config('session.lifetime', 120))->getTimestamp())
            ->pluck('user_id')
            ->unique()
            ->toArray();

        return view('admin.pegawai.index', compact('pegawai', 'activeUserIds'));
    }

    /**
     * Admin mereset password pegawai yang lupa password.
     */
    public function resetPassword(Request $request, $id)
    {
        $request->validate([
            'new_password' => 'required|string|min:6|confirmed',
        ], [
            'new_password.required' => 'Password baru wajib diisi.',
            'new_password.min' => 'Password baru minimal 6 karakter.',
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        $user = User::where('role', 'pegawai')->findOrFail($id);
        $user->password = bcrypt($request->new_password);
        $user->save();

        return back()->with('success', "Password pegawai {$user->name} berhasil direset!");
    }
}
