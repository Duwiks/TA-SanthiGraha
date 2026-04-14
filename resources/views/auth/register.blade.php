@extends('layouts.auth')

@section('title', 'Registrasi - SanthiGraha')
@section('header_title', 'Daftar Akun Pegawai')
@section('header_subtitle', 'SanthiGraha Financial Management')

@section('content')
<form method="POST" action="{{ url('/register') }}" class="space-y-4">
    @csrf
    
    <div>
        <label for="name" class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Lengkap</label>
        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus placeholder="Cth: I Kadek Ari" 
               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all duration-200 @error('name') border-red-500 focus:ring-red-500/20 focus:border-red-500 @enderror">
        @error('name')
            <p class="text-red-500 text-xs font-semibold mt-1.5">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="username" class="block text-sm font-semibold text-slate-700 mb-1.5">Username</label>
        <input id="username" type="text" name="username" value="{{ old('username') }}" required placeholder="Min. 4 huruf" 
               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all duration-200 @error('username') border-red-500 focus:ring-red-500/20 focus:border-red-500 @enderror">
        @error('username')
            <p class="text-red-500 text-xs font-semibold mt-1.5">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="phone" class="block text-sm font-semibold text-slate-700 mb-1.5">No. Telepon</label>
        <input id="phone" type="text" name="phone" value="{{ old('phone') }}" placeholder="Cth: 08123456789" 
               class="w-full px-4 py-2.5 rounded-xl border border-slate-200 bg-white/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all duration-200 @error('phone') border-red-500 focus:ring-red-500/20 focus:border-red-500 @enderror">
        @error('phone')
            <p class="text-red-500 text-xs font-semibold mt-1.5">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="password" class="block text-sm font-semibold text-slate-700 mb-1.5">Password</label>
        <div class="relative">
            <input id="password" type="password" name="password" required autocomplete="new-password" placeholder="Minimal 6 karakter" 
                   class="w-full px-4 py-2.5 pr-12 rounded-xl border border-slate-200 bg-white/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 transition-all duration-200 @error('password') border-red-500 focus:ring-red-500/20 focus:border-red-500 @enderror">
            <button type="button" onclick="togglePassword()" class="absolute inset-y-0 right-0 flex items-center pr-4 text-slate-400 hover:text-brand-600 focus:outline-none transition-colors">
                <i class="ph ph-eye text-xl" id="eyeIcon"></i>
            </button>
        </div>
        @error('password')
            <p class="text-red-500 text-xs font-semibold mt-1.5">{{ $message }}</p>
        @enderror
    </div>

    <button type="submit" class="w-full py-3 px-4 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-xl shadow-lg shadow-brand-500/30 hover:shadow-brand-500/40 hover:-translate-y-0.5 transition-all duration-200 mt-4">
        Daftar Sekarang
    </button>
</form>

<div class="text-center mt-6 text-sm text-slate-500 font-medium">
    Sudah punya akun? <a href="{{ url('/login') }}" class="text-brand-600 hover:text-brand-700 hover:underline font-semibold transition-colors">Masuk di sini</a>
</div>

<script>
    function togglePassword() {
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');
        
        if (passwordInput.type === 'password') {
            passwordInput.type = 'text';
            eyeIcon.classList.remove('ph-eye');
            eyeIcon.classList.add('ph-eye-closed');
        } else {
            passwordInput.type = 'password';
            eyeIcon.classList.remove('ph-eye-closed');
            eyeIcon.classList.add('ph-eye');
        }
    }
</script>
@endsection
