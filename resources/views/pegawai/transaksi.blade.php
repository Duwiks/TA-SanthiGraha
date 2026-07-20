@extends('layouts.pegawai')

@section('title', 'Riwayat Transaksi - SanthiGraha')
@section('page_title', 'Riwayat Pengajuan Transaksi')

@section('content')

    {{-- Header --}}
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Riwayat Transaksi</h2>
            <p class="text-sm text-slate-500 mt-1">Pantau status pengajuan dan riwayat transaksi Anda.</p>
        </div>
        <a href="{{ route('transactions.create') }}"
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-500 text-white font-semibold text-sm hover:bg-emerald-600 hover:shadow-lg hover:shadow-emerald-500/20 transition-all">
            <i class="ph ph-plus text-lg"></i> Ajukan Transaksi
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
            <option value="pengeluaran" @selected(request('type') === 'pengeluaran')>Pengeluaran</option>
        </select>
        <select name="status"
            class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm bg-white focus:ring-2 focus:ring-emerald-400 outline-none">
            <option value="">Semua Status</option>
            <option value="pending" @selected(request('status') === 'pending')>Menunggu Persetujuan</option>
            <option value="approved" @selected(request('status') === 'approved')>Disetujui</option>
            <option value="rejected" @selected(request('status') === 'rejected')>Ditolak</option>
        </select>
        <select name="year"
            class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm bg-white focus:ring-2 focus:ring-emerald-400 outline-none">
            <option value="">Semua Tahun</option>
            @foreach($availableYears as $year)
                <option value="{{ $year }}" @selected(request('year') == $year)>{{ $year }}</option>
            @endforeach
        </select>
        <select name="sort"
            class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm bg-white focus:ring-2 focus:ring-emerald-400 outline-none">
            <option value="latest" @selected(request('sort', 'latest') === 'latest')>Tanggal Terbaru (Nota)</option>
            <option value="oldest" @selected(request('sort') === 'oldest')>Tanggal Terlama (Nota)</option>
            <option value="newest_input" @selected(request('sort') === 'newest_input')>Data Baru Masuk</option>
        </select>
        <button type="submit"
            class="px-5 py-2.5 rounded-xl bg-slate-700 text-white text-sm font-medium hover:bg-slate-800 transition-colors">Cari</button>
        @if(request()->anyFilled(['search', 'type', 'status', 'year']))
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

                            {{-- Bukti --}}
                            <td class="px-5 py-4">
                                @if($trx->receipt_photo)
                                    @if(str_ends_with(strtolower($trx->receipt_photo), '.pdf'))
                                        <a href="{{ asset('storage/' . $trx->receipt_photo) }}" target="_blank"
                                            class="inline-flex items-center gap-1 text-[11px] font-semibold text-red-600 bg-red-50 px-2.5 py-1 rounded-lg hover:bg-red-100 transition-colors border border-red-100">
                                            <i class="ph ph-file-pdf text-base"></i> Lihat PDF
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
                                    @if($trx->rejections->count() > 0)
                                        <div class="mt-1.5 text-[11px] text-red-500 italic text-left px-1">
                                            <i class="ph ph-warning"></i> {{ Str::limit($trx->rejections->last()->reason, 60) }}
                                        </div>
                                    @endif
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
                                    @if(in_array($trx->status, ['pending', 'rejected']))
                                        <a href="{{ route('transactions.edit', $trx->id) }}"
                                            class="p-2 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white transition-colors"
                                            title="Edit & Kirim Ulang">
                                            <i class="ph ph-pencil-simple text-base"></i>
                                        </a>
                                        <form action="{{ route('transactions.destroy', $trx->id) }}" method="POST"
                                            id="delete-form-{{ $trx->id }}" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="button"
                                                onclick="confirmDelete('delete-form-{{ $trx->id }}', 'Apakah Anda yakin ingin menghapus transaksi ini?')"
                                                class="p-2 rounded-lg bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-colors"
                                                title="Hapus">
                                                <i class="ph ph-trash text-base"></i>
                                            </button>
                                        </form>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-slate-50 text-slate-400 text-xs font-semibold border border-slate-100 cursor-not-allowed"
                                            title="Terkunci (Transaksi sudah valid/disetujui)">
                                            <i class="ph ph-lock text-sm"></i> Terkunci
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="w-16 h-16 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="ph ph-receipt text-3xl text-emerald-300"></i>
                                </div>
                                <p class="text-base font-bold text-slate-800">Belum Ada Transaksi</p>
                                <p class="text-sm text-slate-500 mt-1">Klik tombol "Ajukan Transaksi" untuk melaporkan pemasukan
                                    atau pengeluaran.</p>
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

    {{-- Legend --}}
    <div class="mt-5 flex flex-wrap gap-3 text-xs text-slate-500">
        <span class="font-semibold text-slate-600">Status:</span>
        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-amber-400 inline-block"></span> Menunggu
            Persetujuan</span>
        <i class="ph ph-arrow-right text-slate-300"></i>
        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-400 inline-block"></span>
            Disetujui Admin</span>
        <span class="mx-1 text-slate-300">|</span>
        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-red-400 inline-block"></span> Ditolak
            (dapat diedit)</span>
    </div>

@endsection