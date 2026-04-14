@extends('layouts.pegawai')

@section('title', 'Dashboard Pegawai - SanthiGraha')
@section('page_title', 'Dashboard Pegawai')

@section('content')
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">

        <!-- Metric Card 1 -->
        <div
            class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center gap-5 hover:shadow-md transition-shadow">
            <div class="w-14 h-14 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-500">
                <i class="ph ph-receipt text-2xl"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500 mb-1">Transaksi Diajukan</p>
                <h3 class="text-2xl font-bold text-slate-800">{{ $transaksiDiajukan }}</h3>
            </div>
        </div>

        <!-- Metric Card 2 -->
        <div
            class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center gap-5 hover:shadow-md transition-shadow">
            <div class="w-14 h-14 rounded-full bg-amber-50 flex items-center justify-center text-amber-500">
                <i class="ph ph-hourglass-high text-2xl"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500 mb-1">Menunggu Proses Admin</p>
                <h3 class="text-2xl font-bold text-slate-800">{{ $menungguProses }}</h3>
            </div>
        </div>

        <!-- Metric Card 3 -->
        <div
            class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center gap-5 hover:shadow-md transition-shadow">
            <div class="w-14 h-14 rounded-full bg-red-50 flex items-center justify-center text-red-500">
                <i class="ph ph-x-circle text-2xl"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500 mb-1">Transaksi Ditolak</p>
                <h3 class="text-2xl font-bold text-slate-800">{{ $transaksiDitolak }}</h3>
            </div>
        </div>

    </div>

    <!-- Welcome Banner Section -->
    <div
        class="bg-gradient-to-r from-emerald-600 to-teal-500 rounded-3xl p-8 text-white shadow-lg shadow-emerald-500/20 relative overflow-hidden">
        <!-- Abstract Shapes -->
        <div class="absolute -right-10 -top-20 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
        <div class="absolute right-40 -bottom-20 w-40 h-40 bg-white/10 rounded-full blur-2xl"></div>

        <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div>
                <h2 class="text-3xl font-bold mb-2">Halo, {{ auth()->user()->name ?? 'Pegawai' }}! 👋</h2>
                <p class="text-emerald-50 text-lg max-w-xl">Selamat datang di Area Pegawai. Laporkan bukti transaksi, unggah
                    struk pengeluaran/pemasukan melalui sistem manajemen keuangan ini.</p>
            </div>
            <div class="shrink-0 bg-white/20 backdrop-blur-sm px-6 py-4 rounded-2xl border border-white/20 text-center">
                <p class="text-sm font-medium text-emerald-50 mb-1">Tanggal Hari Ini</p>
                <p class="text-xl font-bold">{{ date('d M Y') }}</p>
            </div>
        </div>
    </div>
@endsection