@extends('layouts.admin')

@section('title', 'Rekap & Laporan - SanthiGraha')
@section('page_title', 'Rekapitulasi & Laporan Transaksi')

@section('content')
    <div class="bg-white rounded-2xl p-8 shadow-sm border border-slate-100 flex flex-col items-center justify-center min-h-[500px]">
        <div class="w-20 h-20 bg-indigo-50 text-brand-600 rounded-full flex items-center justify-center mb-6">
            <i class="ph ph-chart-bar text-4xl"></i>
        </div>
        <h2 class="text-2xl font-bold text-slate-800 mb-3">Modul Laporan Keuangan</h2>
        <p class="text-slate-500 text-center max-w-lg mb-8 leading-relaxed">
            Halaman ini nantinya akan difungsikan untuk memfilter transaksi berdasarkan rentang tanggal tertentu, melakukan cetak (Print) laporan ke PDF, serta menampilkan grafik statistik (Chart) arus kas masuk dan keluar perusahaan secara komprehensif.
        </p>
        
        <div class="flex items-center gap-4">
            <a href="{{ route('transactions.index') }}" class="px-6 py-2.5 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition-colors">
                Kembali ke Transaksi
            </a>
            <button disabled class="px-6 py-2.5 bg-brand-600/50 cursor-not-allowed text-white text-sm font-semibold rounded-xl flex items-center gap-2">
                <i class="ph ph-printer"></i> Cetak Laporan (Coming Soon)
            </button>
        </div>
    </div>
@endsection
