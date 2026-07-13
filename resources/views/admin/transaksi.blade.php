@extends('layouts.admin')

@section('title', 'Daftar Transaksi - SanthiGraha')
@section('page_title', 'Buku Kas Transaksi')

@section('content')

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-6">
        <div
            class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center gap-4 min-w-0 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-500 shrink-0">
                <i class="ph ph-trend-up text-xl"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs sm:text-sm font-medium text-slate-500 mb-0.5 truncate" title="Total Pemasukan">Total
                    Pemasukan</p>
                <h3 class="text-sm sm:text-base xl:text-lg font-bold text-emerald-600 truncate"
                    title="Rp {{ number_format($totalPemasukan, 2, ',', '.') }}">Rp
                    {{ number_format($totalPemasukan, 2, ',', '.') }}
                </h3>
            </div>
        </div>
        <div
            class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center gap-4 min-w-0 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-full bg-red-50 flex items-center justify-center text-red-500 shrink-0">
                <i class="ph ph-trend-down text-xl"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs sm:text-sm font-medium text-slate-500 mb-0.5 truncate" title="Total Pengeluaran">Total
                    Pengeluaran</p>
                <h3 class="text-sm sm:text-base xl:text-lg font-bold text-red-600 truncate"
                    title="Rp {{ number_format($totalPengeluaran, 2, ',', '.') }}">Rp
                    {{ number_format($totalPengeluaran, 2, ',', '.') }}
                </h3>
            </div>
        </div>
        <div
            class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center gap-4 min-w-0 hover:shadow-md transition-shadow">
            <div class="w-12 h-12 rounded-full bg-blue-50 flex items-center justify-center text-blue-500 shrink-0">
                <i class="ph ph-wallet text-xl"></i>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-xs sm:text-sm font-medium text-slate-500 mb-0.5 truncate" title="Saldo Berjalan">Saldo
                    Berjalan</p>
                <h3 class="text-sm sm:text-base xl:text-lg font-bold {{ $saldo >= 0 ? 'text-slate-800' : 'text-red-600' }} truncate"
                    title="Rp {{ number_format($saldo, 2, ',', '.') }}">
                    Rp {{ number_format($saldo, 2, ',', '.') }}
                </h3>
            </div>
        </div>
    </div>

    {{-- Header --}}
    <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Daftar Transaksi Disetujui</h2>
            <p class="text-sm text-slate-500 mt-1">Semua transaksi yang sudah tercatat resmi di buku kas.</p>
        </div>
        <a href="{{ route('transactions.create') }}"
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-500 text-white font-semibold text-sm hover:bg-emerald-600 hover:shadow-lg hover:shadow-emerald-500/20 transition-all">
            <i class="ph ph-plus text-lg"></i> Tambah Transaksi
        </a>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('transactions.index') }}" class="mb-5 flex flex-wrap gap-3">
        <div class="relative flex-1 min-w-[180px]">
            <i
                class="ph ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg pointer-events-none"></i>
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Cari proyek, kategori, deskripsi..."
                class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 outline-none bg-white">
        </div>
        <select name="type"
            class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm bg-white focus:ring-2 focus:ring-emerald-400 outline-none">
            <option value="">Semua Tipe</option>
            <option value="pemasukan" @selected(request('type') === 'pemasukan')>Pemasukan</option>
            <option value="pengeluaran" @selected(request('type') === 'pengeluaran')>Pengeluaran</option>
        </select>
        <select name="sort"
            class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm bg-white focus:ring-2 focus:ring-emerald-400 outline-none">
            <option value="latest" @selected(request('sort', 'latest') === 'latest')>Tanggal Terbaru (Nota)</option>
            <option value="oldest" @selected(request('sort') === 'oldest')>Tanggal Terlama (Nota)</option>
            <option value="newest_input" @selected(request('sort') === 'newest_input')>Data Baru Masuk</option>
        </select>
        <button type="submit"
            class="px-5 py-2.5 rounded-xl bg-slate-700 text-white text-sm font-medium hover:bg-slate-800 transition-colors">Cari</button>
        @if(request()->anyFilled(['search', 'type', 'sort']))
            <a href="{{ route('transactions.index') }}"
                class="px-5 py-2.5 rounded-xl bg-slate-100 text-slate-600 text-sm font-medium hover:bg-slate-200 transition-colors">Reset</a>
        @endif
    </form>


    {{-- Tabel --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="px-5 py-4">TANGGAL</th>
                        <th class="px-5 py-4">PROYEK & KATEGORI</th>
                        <th class="px-5 py-4">NOMINAL</th>
                        <th class="px-5 py-4">PENGAJU</th>
                        <th class="px-5 py-4">BUKTI</th>
                        <th class="px-5 py-4 text-center">STATUS</th>
                        <th class="px-5 py-4 text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $trx)
                        <tr class="hover:bg-slate-50 transition-colors">

                            {{-- Tanggal --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="font-medium text-slate-800">
                                    {{ \Carbon\Carbon::parse($trx->transaction_date)->format('d M Y') }}
                                </div>
                                <div class="text-xs text-slate-400 mt-0.5">
                                    {{ \Carbon\Carbon::parse($trx->created_at)->format('H:i') }} WITA
                                </div>
                            </td>

                            {{-- Proyek & Kategori --}}
                            <td class="px-5 py-4">
                                <div class="font-medium text-slate-800">{{ $trx->project->project_name ?? '-' }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">{{ $trx->category->category_name ?? '-' }}</div>
                                @if($trx->description)
                                    <div class="text-[12px] text-slate-400 mt-1 italic">{{ Str::limit($trx->description, 50) }}
                                    </div>
                                @endif
                                @if($trx->nota_merah_id)
                                    <span
                                        class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-50 text-red-500 border border-red-100 mt-1">
                                        <i class="ph ph-note-pencil"></i> Nota Merah
                                    </span>
                                @endif
                            </td>

                            {{-- Nominal --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="font-bold {{ $trx->type === 'pemasukan' ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ $trx->type === 'pemasukan' ? '+' : '-' }} Rp
                                    {{ number_format($trx->amount, 2, ',', '.') }}
                                </span>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <span
                                        class="text-[10px] uppercase font-bold tracking-wide px-2 py-0.5 rounded
                                                                {{ $trx->type === 'pemasukan' ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600' }}">
                                        {{ $trx->type }}
                                    </span>
                                    @if($trx->payment_method)
                                        <span
                                            class="text-[10px] uppercase font-medium tracking-wide text-slate-400">{{ $trx->payment_method }}</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Pengaju --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div
                                        class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs shrink-0">
                                        {{ substr($trx->user->name ?? 'U', 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-medium text-slate-700 text-sm">{{ $trx->user->name ?? '-' }}</div>
                                        @if($trx->approver)
                                            <div class="text-xs text-slate-400">✓ {{ $trx->approver->name }}</div>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Bukti --}}
                            <td class="px-5 py-4">
                                @if($trx->receipt_photo)
                                    @if(str_ends_with(strtolower($trx->receipt_photo), '.pdf'))
                                        <a href="{{ asset('storage/' . $trx->receipt_photo) }}" target="_blank"
                                            class="inline-flex items-center gap-1 text-[11px] font-semibold text-red-600 bg-red-50 px-2.5 py-1 rounded-lg hover:bg-red-100 transition-colors border border-red-100">
                                            <i class="ph ph-file-pdf"></i> Lihat PDF
                                        </a>
                                    @else
                                        <a href="{{ asset('storage/' . $trx->receipt_photo) }}" target="_blank">
                                            <img src="{{ asset('storage/' . $trx->receipt_photo) }}" alt="Bukti"
                                                class="h-12 w-16 object-cover rounded-lg border border-slate-200 hover:opacity-80 transition-opacity">
                                        </a>
                                    @endif
                                @else
                                    <span class="text-xs text-slate-400 italic">—</span>
                                @endif
                            </td>

                            {{-- Status --}}
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                @if($trx->status === 'approved')
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">
                                        <i class="ph ph-check-circle"></i> Disetujui
                                    </span>
                                @elseif($trx->status === 'rejected')
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">
                                        <i class="ph ph-x-circle"></i> Ditolak
                                    </span>
                                @else
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700">
                                        <i class="ph ph-clock"></i> Menunggu
                                    </span>
                                @endif
                            </td>

                            {{-- Aksi --}}
                            <td class="px-5 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <form action="{{ route('transactions.destroy', $trx->id) }}" method="POST"
                                        id="delete-form-{{ $trx->id }}" class="inline">
                                        @csrf @method('DELETE')
                                        @if($trx->nota_merah_id)
                                            <button type="button"
                                                onclick="confirmDelete('delete-form-{{ $trx->id }}', 'Transaksi ini berasal dari Nota Merah. Menghapusnya akan mengembalikan status Nota Merah terkait menjadi Menunggu Verifikasi. Apakah Anda yakin?')"
                                                class="p-2 rounded-lg bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-colors"
                                                title="Hapus">
                                                <i class="ph ph-trash text-base"></i>
                                            </button>
                                        @else
                                            <button type="button"
                                                onclick="confirmDelete('delete-form-{{ $trx->id }}', 'Apakah Anda yakin ingin menghapus transaksi ini secara permanen?')"
                                                class="p-2 rounded-lg bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-colors"
                                                title="Hapus">
                                                <i class="ph ph-trash text-base"></i>
                                            </button>
                                        @endif
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-16 text-center">
                                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="ph ph-receipt text-3xl text-slate-300"></i>
                                </div>
                                <p class="text-base font-bold text-slate-800">Belum Ada Transaksi</p>
                                <p class="text-sm text-slate-500 mt-1">Tidak ada data yang cocok dengan kriteria pencarian.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($transactions->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 text-sm">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>

@endsection