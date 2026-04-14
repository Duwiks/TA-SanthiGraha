@extends('layouts.admin')

@section('title', 'Daftar Transaksi - SanthiGraha')
@section('page_title', 'Semua Transaksi Masuk')

@section('content')
    <!-- Summary Cards (Rekap Transaksi Admin) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center gap-5">
            <div class="w-14 h-14 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-500">
                <i class="ph ph-trend-up text-2xl"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500 mb-1">Total Pemasukan</p>
                <h3 class="text-xl font-bold text-slate-800">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</h3>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center gap-5">
            <div class="w-14 h-14 rounded-full bg-red-50 flex items-center justify-center text-red-500">
                <i class="ph ph-trend-down text-2xl"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500 mb-1">Total Pengeluaran</p>
                <h3 class="text-xl font-bold text-slate-800">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</h3>
            </div>
        </div>

        <div class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 flex items-center gap-5">
            <div class="w-14 h-14 rounded-full bg-brand-50 flex items-center justify-center text-brand-600">
                <i class="ph ph-wallet text-2xl"></i>
            </div>
            <div>
                <p class="text-sm font-medium text-slate-500 mb-1">Saldo Berjalan</p>
                <h3 class="text-xl font-bold text-slate-800">Rp {{ number_format($saldo, 0, ',', '.') }}</h3>
            </div>
        </div>
    </div>

    <!-- Toolbar: Search, Filters -->
    <div class="bg-white rounded-t-2xl p-6 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form action="{{ route('transactions.index') }}" method="GET" class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
            <div class="relative">
                <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari deskripsi, proyek..." class="pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none w-full md:w-64">
            </div>
            
            <select name="type" class="px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none cursor-pointer">
                <option value="">Semua Tipe</option>
                <option value="pemasukan" {{ request('type') == 'pemasukan' ? 'selected' : '' }}>Pemasukan</option>
                <option value="pengeluaran" {{ request('type') == 'pengeluaran' ? 'selected' : '' }}>Pengeluaran</option>
            </select>

            <!-- Status filter removed (Data is verified only) -->
            
            <button type="submit" class="px-5 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-xl transition-colors">
                Filter
            </button>
            @if(request()->anyFilled(['search', 'type', 'status']))
                <a href="{{ route('transactions.index') }}" class="px-5 py-2 bg-red-50 hover:bg-red-100 text-red-600 text-sm font-semibold rounded-xl transition-colors flex items-center justify-center">
                    Reset
                </a>
            @endif
        </form>

        <a href="{{ route('transactions.create') }}" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl shadow-lg shadow-brand-500/30 transition-all flex items-center gap-2 justify-center shrink-0">
            <i class="ph ph-plus-circle text-lg"></i>
            Tambah Transaksi
        </a>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-b-2xl shadow-sm border border-t-0 border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-4">TANGGAL</th>
                        <th class="px-6 py-4">PROYEK & KATEGORI</th>
                        <th class="px-6 py-4">NOMINAL (TIPE)</th>
                        <th class="px-6 py-4">PENGAJU</th>
                        <th class="px-6 py-4 text-center">STATUS</th>
                        <th class="px-6 py-4 text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $trx)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($trx->transaction_date)->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-800">{{ $trx->project->project_name ?? '-' }}</div>
                            <div class="text-xs text-slate-500 mt-0.5">{{ $trx->category->category_name ?? '-' }}</div>
                            @if($trx->description)
                                <div class="text-[12px] text-slate-400 mt-1 italic leading-relaxed">{{ $trx->description }}</div>
                            @endif
                            @if($trx->receipt_photo)
                                <div class="mt-3">
                                    @if(str_ends_with(strtolower($trx->receipt_photo), '.pdf'))
                                        <a href="{{ asset('storage/' . $trx->receipt_photo) }}" target="_blank" class="text-[11px] font-bold text-red-600 bg-red-50 hover:bg-red-100 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg transition-colors w-max border border-red-100">
                                            <i class="ph ph-file-pdf text-base"></i> Dokumen PDF
                                        </a>
                                    @else
                                        <a href="{{ asset('storage/' . $trx->receipt_photo) }}" target="_blank" class="block inline-block">
                                            <img src="{{ asset('storage/' . $trx->receipt_photo) }}" alt="Bukti Transaksi" class="h-16 w-24 object-cover rounded-lg border border-slate-200 shadow-sm hover:opacity-80 transition-opacity" title="Klik untuk perbesar">
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-bold {{ $trx->type == 'pemasukan' ? 'text-emerald-600' : 'text-red-600' }}">
                                Rp {{ number_format($trx->amount, 0, ',', '.') }}
                            </span>
                            <div class="flex items-center gap-2 mt-1.5">
                                <div class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded {{ $trx->type == 'pemasukan' ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600' }}">
                                    {{ $trx->type }}
                                </div>
                                @if($trx->payment_method)
                                    <div class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200" title="Metode Transfer/Pencairan">
                                        <i class="ph ph-wallet"></i> {{ $trx->payment_method }}
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium">{{ $trx->user->name ?? '-' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            @if($trx->status == 'approved')
                                <span class="px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 text-xs font-bold inline-flex items-center gap-1">
                                    <i class="ph ph-check-circle"></i> Disetujui
                                </span>
                            @elseif($trx->status == 'rejected')
                                <span class="px-3 py-1 rounded-full bg-red-100 text-red-700 text-xs font-bold inline-flex items-center gap-1">
                                    <i class="ph ph-x-circle"></i> Ditolak
                                </span>
                            @else
                                <span class="px-3 py-1 rounded-full bg-amber-100 text-amber-700 text-xs font-bold inline-flex items-center gap-1">
                                    <i class="ph ph-hourglass-medium"></i> Pending
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <div class="flex items-center justify-center gap-2">
                                <!-- Approval buttons migrated to dedicated module -->

                                <a href="{{ route('transactions.edit', $trx->id) }}" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-500 hover:text-white transition-colors border border-blue-200" title="Edit Transaksi">
                                    <i class="ph ph-pencil-simple"></i>
                                </a>
                                <form action="{{ route('transactions.destroy', $trx->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus transaksi ini permanen?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center hover:bg-slate-500 hover:text-white transition-colors border border-slate-200" title="Hapus Permanen">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                            <i class="ph ph-receipt text-5xl mb-4 text-slate-200"></i>
                            <p class="text-base font-medium text-slate-500">Belum ada transaksi</p>
                            <p class="text-sm">Tidak ada entri data yang cocok dengan kriteria pencarian/filter.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination Links -->
        @if($transactions->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 text-sm">
            {{ $transactions->links() }}
        </div>
        @endif
    </div>

@endsection
