@extends('layouts.admin')

@section('title', 'Data Akun Pegawai - SanthiGraha')
@section('page_title', 'Data Akun Pegawai')

@section('content')
    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center text-blue-500">
                <i class="ph ph-users text-xl"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500 mb-0.5">Total Pegawai</p>
                <h3 class="text-xl font-bold text-slate-800">{{ $pegawai->total() }}</h3>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-500">
                <i class="ph ph-wifi-high text-xl"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500 mb-0.5">Sedang Online</p>
                <h3 class="text-xl font-bold text-emerald-600">{{ count(array_intersect($activeUserIds, $pegawai->pluck('id')->toArray())) }}</h3>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400">
                <i class="ph ph-wifi-slash text-xl"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500 mb-0.5">Offline</p>
                <h3 class="text-xl font-bold text-slate-600">{{ $pegawai->total() - count(array_intersect($activeUserIds, $pegawai->pluck('id')->toArray())) }}</h3>
            </div>
        </div>
    </div>

    <!-- Toolbar: Search -->
    <div class="bg-white rounded-t-2xl p-6 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form action="{{ route('pegawai.index') }}" method="GET" class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
            <div class="relative">
                <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, username, atau telepon..." class="pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none w-full md:w-80">
            </div>

            <button type="submit" class="px-5 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-xl transition-colors">
                Cari
            </button>
            @if(request()->filled('search'))
                <a href="{{ route('pegawai.index') }}" class="px-5 py-2 bg-red-50 hover:bg-red-100 text-red-600 text-sm font-semibold rounded-xl transition-colors flex items-center justify-center">
                    Reset
                </a>
            @endif
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-b-2xl shadow-sm border border-t-0 border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-4 w-12 text-center">NO</th>
                        <th class="px-6 py-4">NAMA</th>
                        <th class="px-6 py-4">USERNAME</th>
                        <th class="px-6 py-4">TELEPON</th>
                        <th class="px-6 py-4 text-center">STATUS</th>
                        <th class="px-6 py-4">TERDAFTAR</th>
                        <th class="px-6 py-4 text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pegawai as $index => $user)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 text-center">{{ $index + 1 + ($pegawai->currentPage() - 1) * $pegawai->perPage() }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-brand-500 to-indigo-400 flex items-center justify-center text-white font-bold text-sm shrink-0">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <span class="font-medium text-slate-800">{{ $user->name }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <code class="px-2 py-1 bg-slate-100 rounded-md text-xs font-mono text-slate-600">{{ $user->username }}</code>
                        </td>
                        <td class="px-6 py-4">{{ $user->phone ?: '-' }}</td>
                        <td class="px-6 py-4 text-center">
                            @if(in_array($user->id, $activeUserIds))
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-600 border border-emerald-200">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    Online
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-500 border border-slate-200">
                                    <span class="w-2 h-2 rounded-full bg-slate-400"></span>
                                    Offline
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-slate-500">
                            {{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            <button type="button" onclick="showResetPasswordPopup({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-50 text-amber-600 text-xs font-semibold hover:bg-amber-500 hover:text-white transition-colors border border-amber-200" title="Reset Password">
                                <i class="ph ph-key"></i>
                                Reset Password
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-slate-400">
                            <i class="ph ph-user-circle text-5xl mb-4 text-slate-200"></i>
                            <p class="text-base font-medium text-slate-500">Belum ada akun pegawai</p>
                            <p class="text-sm">Belum ada pegawai yang terdaftar dalam sistem.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Links -->
        @if($pegawai->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 text-sm">
            {{ $pegawai->links() }}
        </div>
        @endif
    </div>

    <!-- Hidden form for reset password -->
    <form id="resetPasswordForm" method="POST" class="hidden">
        @csrf
        @method('PUT')
        <input type="hidden" name="new_password" id="reset_new_password">
        <input type="hidden" name="new_password_confirmation" id="reset_new_password_confirmation">
    </form>

    <script>
        function showResetPasswordPopup(userId, userName) {
            Swal.fire({
                title: '<i class="ph ph-key" style="color:#f59e0b;font-size:28px;"></i><br>Reset Password',
                html: `
                    <div style="text-align:left; margin-top:8px;">
                        <div style="padding:10px 14px;background:#f1f5f9;border:1px solid #e2e8f0;border-radius:10px;margin-bottom:16px;">
                            <p style="font-size:13px;color:#64748b;margin:0;">Reset password untuk pegawai:</p>
                            <p style="font-size:15px;font-weight:700;color:#1e293b;margin:4px 0 0 0;">${userName}</p>
                        </div>
                        <label style="display:block;font-size:13px;font-weight:600;color:#475569;margin-bottom:6px;">Password Baru</label>
                        <input type="password" id="swal_reset_password" class="swal2-input" placeholder="Minimal 6 karakter" style="width:100%;margin:0 0 16px 0;box-sizing:border-box;font-size:14px;">
                        <label style="display:block;font-size:13px;font-weight:600;color:#475569;margin-bottom:6px;">Konfirmasi Password Baru</label>
                        <input type="password" id="swal_reset_password_confirm" class="swal2-input" placeholder="Ulangi password baru" style="width:100%;margin:0;box-sizing:border-box;font-size:14px;">
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: '<i class="ph ph-floppy-disk"></i> Reset Password',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#f59e0b',
                cancelButtonColor: '#e2e8f0',
                customClass: {
                    cancelButton: 'swal-cancel-dark',
                    popup: 'swal-rounded',
                },
                focusConfirm: false,
                preConfirm: () => {
                    const newPw = document.getElementById('swal_reset_password').value;
                    const confirmPw = document.getElementById('swal_reset_password_confirm').value;

                    if (!newPw || newPw.length < 6) {
                        Swal.showValidationMessage('Password baru minimal 6 karakter');
                        return false;
                    }
                    if (newPw !== confirmPw) {
                        Swal.showValidationMessage('Konfirmasi password tidak cocok');
                        return false;
                    }
                    return { newPw, confirmPw };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('resetPasswordForm');
                    form.action = `/pegawai/${userId}/reset-password`;
                    document.getElementById('reset_new_password').value = result.value.newPw;
                    document.getElementById('reset_new_password_confirmation').value = result.value.confirmPw;
                    form.submit();
                }
            });
        }
    </script>

    <style>
        .swal-cancel-dark { color: #475569 !important; }
        .swal-rounded { border-radius: 16px !important; }
    </style>
@endsection
