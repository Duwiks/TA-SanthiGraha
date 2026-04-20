@extends('layouts.admin')

@section('title', 'Pengaturan Akun - SanthiGraha')
@section('page_title', 'Pengaturan Akun')

@section('content')
    <div class="max-w-3xl mx-auto space-y-6">

        <!-- Informasi Akun -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-500">
                    <i class="ph ph-user-circle text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Informasi Akun</h3>
                    <p class="text-sm text-slate-500">Detail data akun Anda di sistem</p>
                </div>
            </div>
            <div class="p-6">
                <!-- Profile Header -->
                <div class="flex items-center gap-5 mb-8 pb-6 border-b border-slate-100 border-dashed">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center text-white font-bold text-3xl shadow-lg shadow-indigo-500/20">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div>
                        <h4 class="text-xl font-bold text-slate-800">{{ $user->name }}</h4>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 mt-1.5 rounded-full text-xs font-semibold bg-indigo-50 text-indigo-600 border border-indigo-200 capitalize">
                            <i class="ph ph-shield-check text-sm"></i>
                            {{ $user->role }}
                        </span>
                    </div>
                </div>

                <!-- Info Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Nama Lengkap</p>
                        <p class="text-sm font-semibold text-slate-800">{{ $user->name }}</p>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Username</p>
                        <p class="text-sm font-semibold text-slate-800">{{ $user->username }}</p>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Nomor Telepon</p>
                        <p class="text-sm font-semibold text-slate-800">{{ $user->phone ?: '-' }}</p>
                    </div>
                    <div class="bg-slate-50 rounded-xl p-4 border border-slate-100">
                        <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1.5">Terdaftar Sejak</p>
                        <p class="text-sm font-semibold text-slate-800">{{ $user->created_at ? $user->created_at->format('d M Y, H:i') : '-' }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Keamanan Akun -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="px-6 py-5 flex items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-amber-50 flex items-center justify-center text-amber-500">
                        <i class="ph ph-lock-key text-xl"></i>
                    </div>
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">Keamanan Akun</h3>
                        <p class="text-sm text-slate-500">Kelola password akun Anda</p>
                    </div>
                </div>
                <button type="button" onclick="showChangePasswordPopup()"
                    class="px-5 py-2.5 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold rounded-xl shadow-lg shadow-amber-500/20 transition-all flex items-center gap-2">
                    <i class="ph ph-key text-lg"></i>
                    Ganti Password
                </button>
            </div>
        </div>

    </div>

    <!-- Hidden form for password change submission -->
    <form id="changePasswordForm" action="{{ route('account.password') }}" method="POST" class="hidden">
        @csrf
        @method('PUT')
        <input type="hidden" name="current_password" id="form_current_password">
        <input type="hidden" name="new_password" id="form_new_password">
        <input type="hidden" name="new_password_confirmation" id="form_new_password_confirmation">
    </form>

    <script>
        function showChangePasswordPopup() {
            Swal.fire({
                title: '<i class="ph ph-lock-key" style="color:#f59e0b;font-size:28px;"></i><br>Ganti Password',
                html: `
                    <div style="text-align:left; margin-top:8px;">
                        <label style="display:block;font-size:13px;font-weight:600;color:#475569;margin-bottom:6px;">Password Lama</label>
                        <input type="password" id="swal_current_password" class="swal2-input" placeholder="Masukkan password lama" style="width:100%;margin:0 0 16px 0;box-sizing:border-box;font-size:14px;">
                        <label style="display:block;font-size:13px;font-weight:600;color:#475569;margin-bottom:6px;">Password Baru</label>
                        <input type="password" id="swal_new_password" class="swal2-input" placeholder="Minimal 6 karakter" style="width:100%;margin:0 0 16px 0;box-sizing:border-box;font-size:14px;">
                        <label style="display:block;font-size:13px;font-weight:600;color:#475569;margin-bottom:6px;">Konfirmasi Password Baru</label>
                        <input type="password" id="swal_new_password_confirmation" class="swal2-input" placeholder="Ulangi password baru" style="width:100%;margin:0;box-sizing:border-box;font-size:14px;">
                        <p style="font-size:12px;color:#94a3b8;margin-top:12px;"><i class="ph ph-info" style="margin-right:4px;"></i>Lupa password lama? Silakan hubungi tim IT / Super Admin untuk reset password.</p>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: '<i class="ph ph-floppy-disk"></i> Simpan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#e2e8f0',
                customClass: {
                    cancelButton: 'swal-cancel-dark',
                    popup: 'swal-rounded',
                },
                focusConfirm: false,
                preConfirm: () => {
                    const current = document.getElementById('swal_current_password').value;
                    const newPw = document.getElementById('swal_new_password').value;
                    const confirm = document.getElementById('swal_new_password_confirmation').value;

                    if (!current) {
                        Swal.showValidationMessage('Password lama wajib diisi');
                        return false;
                    }
                    if (!newPw || newPw.length < 6) {
                        Swal.showValidationMessage('Password baru minimal 6 karakter');
                        return false;
                    }
                    if (newPw !== confirm) {
                        Swal.showValidationMessage('Konfirmasi password baru tidak cocok');
                        return false;
                    }
                    return { current, newPw, confirm };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('form_current_password').value = result.value.current;
                    document.getElementById('form_new_password').value = result.value.newPw;
                    document.getElementById('form_new_password_confirmation').value = result.value.confirm;
                    document.getElementById('changePasswordForm').submit();
                }
            });
        }

        // Show error popup if validation failed on server side
        @if($errors->any())
            Swal.fire({
                icon: 'error',
                title: 'Gagal Mengubah Password',
                html: '{!! implode("<br>", $errors->all()) !!}',
                confirmButtonColor: '#4f46e5',
            });
        @endif
    </script>

    <style>
        .swal-cancel-dark { color: #475569 !important; }
        .swal-rounded { border-radius: 16px !important; }
    </style>
@endsection
