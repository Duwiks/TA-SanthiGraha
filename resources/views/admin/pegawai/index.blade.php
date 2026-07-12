@extends('layouts.admin')

@section('title', 'Data Akun Pegawai - SanthiGraha')
@section('page_title', 'Data Akun Pegawai')

@section('content')

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-6">
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center text-blue-500 shrink-0">
                <i class="ph ph-users text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500 mb-0.5">Total Pegawai</p>
                <h3 class="text-lg font-bold text-slate-800">{{ $totalPegawai }}</h3>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-500 shrink-0">
                <i class="ph ph-wifi-high text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500 mb-0.5">Sedang Online</p>
                <h3 class="text-lg font-bold text-emerald-600">{{ $totalOnline }}</h3>
            </div>
        </div>
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-slate-100 flex items-center justify-center text-slate-400 shrink-0">
                <i class="ph ph-wifi-slash text-xl"></i>
            </div>
            <div>
                <p class="text-xs font-medium text-slate-500 mb-0.5">Offline</p>
                <h3 class="text-lg font-bold text-slate-600">{{ $totalOffline }}</h3>
            </div>
        </div>
    </div>

    {{-- Header --}}
    <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Daftar Akun Pegawai</h2>
            <p class="text-sm text-slate-500 mt-1">Kelola akun dan akses pegawai lapangan.</p>
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('pegawai.index') }}" class="mb-5 flex flex-wrap gap-3">
        <div class="relative flex-1 min-w-[180px]">
            <i
                class="ph ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg pointer-events-none"></i>
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Cari nama, username, atau telepon..."
                class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 outline-none bg-white">
        </div>
        <button type="submit"
            class="px-5 py-2.5 rounded-xl bg-slate-700 text-white text-sm font-medium hover:bg-slate-800 transition-colors">Cari</button>
        @if(request()->filled('search'))
            <a href="{{ route('pegawai.index') }}"
                class="px-5 py-2.5 rounded-xl bg-slate-100 text-slate-600 text-sm font-medium hover:bg-slate-200 transition-colors">Reset</a>
        @endif
    </form>

    {{-- Tabel --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="px-5 py-4 text-center w-12">NO</th>
                        <th class="px-5 py-4">NAMA PEGAWAI</th>
                        <th class="px-5 py-4">USERNAME</th>
                        <th class="px-5 py-4">TELEPON</th>
                        <th class="px-5 py-4 text-center">STATUS</th>
                        <th class="px-5 py-4">TERDAFTAR</th>
                        <th class="px-5 py-4 text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($pegawai as $index => $user)
                        <tr class="hover:bg-slate-50 transition-colors">

                            {{-- No --}}
                            <td class="px-5 py-4 text-center text-slate-400 font-medium">
                                {{ $index + 1 + ($pegawai->currentPage() - 1) * $pegawai->perPage() }}
                            </td>

                            {{-- Nama --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div
                                        class="w-9 h-9 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm shrink-0">
                                        {{ strtoupper(substr($user->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-slate-800">{{ $user->name }}</div>
                                        @if(in_array($user->id, $activeUserIds))
                                            <div class="text-[11px] text-emerald-500 font-medium flex items-center gap-1 mt-0.5">
                                                <span
                                                    class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse inline-block"></span>
                                                Aktif sekarang
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Username --}}
                            <td class="px-5 py-4">
                                <code
                                    class="px-2 py-1 bg-slate-100 rounded-md text-xs font-mono text-slate-600">{{ $user->username }}</code>
                            </td>

                            {{-- Telepon --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                @if($user->phone)
                                    <div class="flex items-center gap-1.5 text-slate-700">
                                        <i class="ph ph-phone text-slate-400 text-sm"></i>
                                        {{ $user->phone }}
                                    </div>
                                @else
                                    <span class="text-slate-400 italic text-xs">—</span>
                                @endif
                            </td>

                            {{-- Status Online --}}
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                @if(in_array($user->id, $activeUserIds))
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse inline-block"></span>
                                        Online
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-500">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400 inline-block"></span>
                                        Offline
                                    </span>
                                @endif
                            </td>

                            {{-- Terdaftar --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="font-medium text-slate-700">
                                    {{ $user->created_at ? $user->created_at->format('d M Y') : '-' }}</div>
                                <div class="text-xs text-slate-400 mt-0.5">
                                    {{ $user->created_at ? $user->created_at->format('H:i') . ' WITA' : '' }}</div>
                            </td>

                            {{-- Aksi --}}
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                <button type="button"
                                    onclick="showResetPasswordPopup({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-50 text-amber-600 text-xs font-semibold hover:bg-amber-500 hover:text-white transition-colors border border-amber-200"
                                    title="Reset Password">
                                    <i class="ph ph-key text-sm"></i> Reset Password
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="ph ph-user-circle text-3xl text-slate-300"></i>
                                </div>
                                <p class="text-base font-bold text-slate-800">Belum Ada Pegawai</p>
                                <p class="text-sm text-slate-500 mt-1">Belum ada akun pegawai yang terdaftar dalam sistem.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($pegawai->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 text-sm">
                {{ $pegawai->links() }}
            </div>
        @endif
    </div>

    {{-- Hidden form for reset password --}}
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
                        <div style="position: relative; margin: 0 0 16px 0; width: 100%;">
                            <input type="password" id="swal_reset_password" class="swal2-input" placeholder="Minimal 6 karakter" style="width:100%;margin:0;box-sizing:border-box;font-size:14px;padding-right:40px;">
                            <i class="ph ph-eye-slash" id="toggle_reset_password" onclick="togglePasswordVisibility('swal_reset_password', 'toggle_reset_password')" style="position:absolute;right:16px;top:50%;transform:translateY(-50%);cursor:pointer;color:#94a3b8;font-size:20px;z-index:10;"></i>
                        </div>
                        <label style="display:block;font-size:13px;font-weight:600;color:#475569;margin-bottom:6px;">Konfirmasi Password Baru</label>
                        <div style="position: relative; margin: 0 0 16px 0; width: 100%;">
                            <input type="password" id="swal_reset_password_confirm" class="swal2-input" placeholder="Ulangi password baru" style="width:100%;margin:0;box-sizing:border-box;font-size:14px;padding-right:40px;">
                            <i class="ph ph-eye-slash" id="toggle_reset_password_confirm" onclick="togglePasswordVisibility('swal_reset_password_confirm', 'toggle_reset_password_confirm')" style="position:absolute;right:16px;top:50%;transform:translateY(-50%);cursor:pointer;color:#94a3b8;font-size:20px;z-index:10;"></i>
                        </div>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: '<i class="ph ph-floppy-disk"></i> Reset Password',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#f59e0b',
                cancelButtonColor: '#e2e8f0',
                customClass: { cancelButton: 'swal-cancel-dark', popup: 'swal-rounded' },
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

        function togglePasswordVisibility(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('ph-eye-slash');
                icon.classList.add('ph-eye');
            } else {
                input.type = 'password';
                icon.classList.remove('ph-eye');
                icon.classList.add('ph-eye-slash');
            }
        }
    </script>

    <style>
        .swal-cancel-dark {
            color: #475569 !important;
        }

        .swal-rounded {
            border-radius: 16px !important;
        }
    </style>
@endsection