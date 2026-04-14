@extends('layouts.auth')

@section('title', 'Login - SanthiGraha')
@section('header_title', 'SanthiGraha')
@section('header_subtitle', 'Sistem Manajemen Keuangan Konstruksi')

@section('content')
<form method="POST" action="{{ url('/login') }}" class="space-y-5">
    @csrf
    
    <div>
        <label for="username" class="block text-sm font-semibold text-slate-700 mb-2">Username</label>
        <input id="username" type="text" name="username" value="{{ old('username') }}" required autofocus placeholder="Masukkan username" 
               class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-white/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all duration-200 @error('username') border-red-500 focus:ring-red-500/20 focus:border-red-500 @enderror">
        @error('username')
            <p class="text-red-500 text-xs font-semibold mt-2">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">Password</label>
        <div class="relative">
            <input id="password" type="password" name="password" required autocomplete="current-password" placeholder="Masukkan password" 
                   class="w-full px-4 py-3 pr-12 rounded-xl border border-slate-200 bg-white/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all duration-200 @error('password') border-red-500 focus:ring-red-500/20 focus:border-red-500 @enderror">
            
            <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 hover:text-brand-600 focus:outline-none transition-colors">
                <i class="ph ph-eye text-xl" id="eyeIcon"></i>
            </button>
        </div>
        @error('password')
            <p class="text-red-500 text-xs font-semibold mt-2">{{ $message }}</p>
        @enderror
    </div>

    <button type="submit" class="w-full py-3.5 px-4 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-xl shadow-lg shadow-brand-500/30 hover:shadow-brand-500/40 hover:-translate-y-0.5 transition-all duration-200 mt-2">
        Masuk Sistem
    </button>
</form>

<div class="text-center mt-8 text-sm text-slate-500 font-medium">
    Belum punya akun pegawai? <a href="{{ url('/register') }}" class="text-brand-600 hover:text-brand-700 hover:underline font-semibold transition-colors">Daftar di sini</a>
</div>

<script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            // Ubah icon menjadi mata tertutup
            eyeIcon.classList.remove('ph-eye');
            eyeIcon.classList.add('ph-eye-closed');
        } else {
            passwordInput.type = 'password';
            // Ubah icon kembali menjadi mata terbuka
            eyeIcon.classList.remove('ph-eye-closed');
            eyeIcon.classList.add('ph-eye');
        }
    }
</script>
@endsection
