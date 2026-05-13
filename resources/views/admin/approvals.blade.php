@extends('layouts.admin')

@section('title', 'Approval Transaksi - SanthiGraha')
@section('page_title', 'Approval Transaksi')

@section('content')

    {{-- ================================================================
    BAGIAN 1: ANTREAN PENDING
    ================================================================ --}}
    <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                <span class="w-2 h-5 bg-amber-400 rounded-full inline-block"></span>
                Antrean Menunggu Persetujuan
            </h2>
            <p class="text-sm text-slate-500 mt-1 ml-4">Transaksi yang diajukan pegawai dan butuh tindakan Anda.</p>
        </div>
        @if($transactions->total() > 0)
            <div
                class="flex items-center gap-2 px-4 py-2 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 text-sm font-bold">
                <i class="ph ph-clock text-base"></i>
                {{ $transactions->total() }} menunggu
            </div>
        @endif
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mb-10">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-amber-50/60 text-slate-500 font-semibold border-b border-amber-100">
                    <tr>
                        <th class="px-6 py-4">TANGGAL PENGAJUAN</th>
                        <th class="px-6 py-4">PROYEK & KATEGORI</th>
                        <th class="px-6 py-4">NOMINAL (TIPE)</th>
                        <th class="px-6 py-4">PENGAJU</th>
                        <th class="px-6 py-4 text-center">TINDAKAN</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $trx)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-medium text-slate-800">
                                    {{ \Carbon\Carbon::parse($trx->created_at)->format('d M Y, H:i') }}</div>
                                <div class="text-xs text-slate-400 mt-1">Trx:
                                    {{ \Carbon\Carbon::parse($trx->transaction_date)->format('d/m/Y') }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-medium text-slate-800">{{ $trx->project->project_name ?? '-' }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">{{ $trx->category->category_name ?? '-' }}</div>
                                @if($trx->description)
                                    <div class="text-[12px] text-slate-400 mt-1 italic">{{ $trx->description }}</div>
                                @endif
                                @if($trx->receipt_photo)
                                    <div class="mt-3">
                                        @if(str_ends_with(strtolower($trx->receipt_photo), '.pdf'))
                                            <a href="{{ asset('storage/' . $trx->receipt_photo) }}" target="_blank"
                                                class="text-[11px] font-bold text-red-600 bg-red-50 hover:bg-red-100 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg transition-colors border border-red-100">
                                                <i class="ph ph-file-pdf text-base"></i> Dokumen PDF
                                            </a>
                                        @else
                                            <a href="{{ asset('storage/' . $trx->receipt_photo) }}" target="_blank">
                                                <img src="{{ asset('storage/' . $trx->receipt_photo) }}" alt="Bukti"
                                                    class="h-16 w-24 object-cover rounded-lg border border-slate-200 shadow-sm hover:opacity-80 transition-opacity">
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
                                    <div
                                        class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded {{ $trx->type == 'pemasukan' ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600' }}">
                                        {{ $trx->type }}
                                    </div>
                                    @if($trx->payment_method)
                                        <div
                                            class="text-[10px] font-bold tracking-wider px-2 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200">
                                            <i class="ph ph-wallet"></i> {{ $trx->payment_method }}
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div
                                        class="w-8 h-8 rounded-full bg-brand-500/10 text-brand-600 flex items-center justify-center font-bold text-xs">
                                        {{ substr($trx->user->name ?? 'U', 0, 1) }}
                                    </div>
                                    <span class="font-medium text-slate-700">{{ $trx->user->name ?? '-' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-3">
                                    <form action="{{ route('transactions.approve', $trx->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit"
                                            class="px-4 py-2 rounded-xl bg-emerald-500 text-white font-medium text-sm flex items-center gap-2 hover:bg-emerald-600 hover:shadow-lg hover:shadow-emerald-500/20 transition-all">
                                            <i class="ph ph-check-circle text-lg"></i> Setujui
                                        </button>
                                    </form>
                                    <button onclick="rejectTransaction({{ $trx->id }})"
                                        class="px-4 py-2 rounded-xl bg-red-50 text-red-600 font-medium text-sm flex items-center gap-2 hover:bg-red-500 hover:text-white transition-all border border-red-100 hover:border-red-500">
                                        <i class="ph ph-x-circle text-lg"></i> Tolak
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-14 text-center">
                                <div class="w-16 h-16 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="ph ph-check-square text-3xl text-emerald-300"></i>
                                </div>
                                <p class="text-base font-bold text-slate-800">Antrean Bersih</p>
                                <p class="text-sm text-slate-500 mt-1">Tidak ada transaksi yang menunggu persetujuan saat ini.
                                </p>
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


    {{-- ================================================================
    BAGIAN 2: RIWAYAT — ACTIVITY FEED FORMAT
    ================================================================ --}}
    <div class="mb-5 flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                <span class="w-2 h-5 bg-brand-500 rounded-full inline-block"></span>
                Riwayat Persetujuan
            </h2>
            <p class="text-sm text-slate-500 mt-1 ml-4">Log semua transaksi yang sudah diproses oleh admin.</p>
        </div>

        {{-- Filter Tab + Counter --}}
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('approvals.index') }}"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold transition-all
                   {{ !request('filter_status') ? 'bg-brand-500 text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-600 hover:border-brand-400 hover:text-brand-600' }}">
                <i class="ph ph-list-bullets"></i>
                Semua
                <span class="ml-1 text-[11px] font-bold px-1.5 py-0.5 rounded-full
                    {{ !request('filter_status') ? 'bg-white/25 text-white' : 'bg-slate-100 text-slate-500' }}">
                    {{ $historyApprovedCount + $historyRejectedCount }}
                </span>
            </a>
            <a href="{{ route('approvals.index', ['filter_status' => 'approved']) }}"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold transition-all
                   {{ request('filter_status') === 'approved' ? 'bg-emerald-500 text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-600 hover:border-emerald-400 hover:text-emerald-600' }}">
                <i class="ph ph-check-circle"></i>
                Disetujui
                <span
                    class="ml-1 text-[11px] font-bold px-1.5 py-0.5 rounded-full
                    {{ request('filter_status') === 'approved' ? 'bg-white/25 text-white' : 'bg-emerald-50 text-emerald-600' }}">
                    {{ $historyApprovedCount }}
                </span>
            </a>
            <a href="{{ route('approvals.index', ['filter_status' => 'rejected']) }}"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold transition-all
                   {{ request('filter_status') === 'rejected' ? 'bg-red-500 text-white shadow-sm' : 'bg-white border border-slate-200 text-slate-600 hover:border-red-400 hover:text-red-600' }}">
                <i class="ph ph-x-circle"></i>
                Ditolak
                <span class="ml-1 text-[11px] font-bold px-1.5 py-0.5 rounded-full
                    {{ request('filter_status') === 'rejected' ? 'bg-white/25 text-white' : 'bg-red-50 text-red-600' }}">
                    {{ $historyRejectedCount }}
                </span>
            </a>
        </div>
    </div>

    {{-- Activity Feed --}}
    @if($history->isEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 px-6 py-16 text-center">
            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="ph ph-clock-clockwise text-3xl text-slate-300"></i>
            </div>
            <p class="text-base font-bold text-slate-800">Belum Ada Riwayat</p>
            <p class="text-sm text-slate-500 mt-1">Riwayat akan muncul setelah Anda memproses transaksi.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($history as $trx)
                @php
                    $isApproved = $trx->status === 'approved';
                    $lastRejection = $trx->rejections->last();
                @endphp

                <div class="bg-white rounded-2xl shadow-sm border {{ $isApproved ? 'border-slate-100' : 'border-red-100' }} overflow-hidden
                                hover:shadow-md transition-shadow duration-200 group">
                    <div class="flex items-stretch">

                        {{-- Garis Status Kiri --}}
                        <div class="w-1 flex-shrink-0 {{ $isApproved ? 'bg-emerald-400' : 'bg-red-400' }}"></div>

                        {{-- Isi Card --}}
                        <div class="flex-1 px-5 py-4">
                            <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3">

                                {{-- Kiri: Info Transaksi --}}
                                <div class="flex-1 min-w-0">

                                    {{-- Row 1: Status Badge + Judul --}}
                                    <div class="flex items-center gap-2 flex-wrap mb-2">
                                        @if($isApproved)
                                            <span
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-emerald-100 text-emerald-700">
                                                <i class="ph ph-check-circle text-sm"></i> Disetujui
                                            </span>
                                        @else
                                            <span
                                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg text-[11px] font-bold bg-red-100 text-red-700">
                                                <i class="ph ph-x-circle text-sm"></i> Ditolak
                                            </span>
                                        @endif

                                        <span class="font-semibold text-slate-800 text-sm truncate">
                                            {{ $trx->project->project_name ?? '-' }}
                                        </span>
                                        <span class="text-slate-400 text-xs">·</span>
                                        <span class="text-xs text-slate-500">{{ $trx->category->category_name ?? '-' }}</span>

                                        @if($trx->nota_merah_id)
                                            <span
                                                class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-50 text-red-500 border border-red-100">
                                                <i class="ph ph-note-pencil"></i> Nota Merah
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Row 2: Nominal + Tipe + Metode --}}
                                    <div class="flex items-center gap-3 flex-wrap mb-2.5">
                                        <span
                                            class="font-bold text-base {{ $trx->type == 'pemasukan' ? 'text-emerald-600' : 'text-red-600' }}">
                                            Rp {{ number_format($trx->amount, 0, ',', '.') }}
                                        </span>
                                        <span
                                            class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded
                                                {{ $trx->type == 'pemasukan' ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600' }}">
                                            {{ $trx->type }}
                                        </span>
                                        @if($trx->payment_method)
                                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-slate-100 text-slate-500">
                                                <i class="ph ph-wallet"></i> {{ $trx->payment_method }}
                                            </span>
                                        @endif
                                        @if($trx->description)
                                            <span class="text-xs text-slate-400 italic truncate max-w-xs">
                                                "{{ Str::limit($trx->description, 60) }}"
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Row 3: Pengaju + Tanggal Transaksi --}}
                                    <div class="flex items-center gap-4 flex-wrap text-xs text-slate-500">
                                        <div class="flex items-center gap-1.5">
                                            <div
                                                class="w-5 h-5 rounded-full bg-brand-500/10 text-brand-600 flex items-center justify-center font-bold text-[9px]">
                                                {{ substr($trx->user->name ?? 'U', 0, 1) }}
                                            </div>
                                            <span class="font-medium text-slate-600">{{ $trx->user->name ?? '-' }}</span>
                                        </div>
                                        <div class="flex items-center gap-1 text-slate-400">
                                            <i class="ph ph-calendar text-xs"></i>
                                            Tanggal transaksi: {{ \Carbon\Carbon::parse($trx->transaction_date)->format('d M Y') }}
                                        </div>
                                    </div>

                                    {{-- Row 4: Alasan Penolakan (hanya jika ditolak) --}}
                                    @if(!$isApproved && $lastRejection)
                                        <div class="mt-3 flex items-start gap-2 bg-red-50 border border-red-100 rounded-xl px-3 py-2.5">
                                            <i class="ph ph-warning text-red-400 text-sm flex-shrink-0 mt-0.5"></i>
                                            <div>
                                                <span class="text-[10px] font-bold text-red-500 uppercase tracking-wide">Alasan
                                                    Penolakan</span>
                                                <p class="text-xs text-red-700 mt-0.5 leading-relaxed">{{ $lastRejection->reason }}</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                {{-- Kanan: Foto Bukti + Admin yang Proses + Waktu --}}
                                <div class="flex flex-row md:flex-col items-start md:items-end gap-4 md:gap-3 flex-shrink-0">

                                    {{-- Foto Bukti --}}
                                    @if($trx->receipt_photo)
                                        <div>
                                            @if(str_ends_with(strtolower($trx->receipt_photo), '.pdf'))
                                                <a href="{{ asset('storage/' . $trx->receipt_photo) }}" target="_blank"
                                                    class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl bg-red-50 hover:bg-red-100 text-red-600 text-xs font-semibold transition-colors border border-red-100">
                                                    <i class="ph ph-file-pdf text-base"></i> Lihat PDF
                                                </a>
                                            @else
                                                <a href="{{ asset('storage/' . $trx->receipt_photo) }}" target="_blank"
                                                    class="block group/img">
                                                    <img src="{{ asset('storage/' . $trx->receipt_photo) }}" alt="Bukti"
                                                        class="h-16 w-20 object-cover rounded-xl border border-slate-200 shadow-sm group-hover/img:opacity-75 group-hover/img:shadow-md transition-all">
                                                    <span class="text-[10px] text-slate-400 mt-1 block text-center">Klik perbesar</span>
                                                </a>
                                            @endif
                                        </div>
                                    @endif

                                    {{-- Admin yang Proses + Waktu --}}
                                    <div class="text-right">
                                        @if($trx->approver)
                                            <div class="flex items-center gap-1.5 justify-end mb-1">
                                                <div
                                                    class="w-5 h-5 rounded-full bg-brand-500 text-white flex items-center justify-center font-bold text-[9px]">
                                                    {{ substr($trx->approver->name, 0, 1) }}
                                                </div>
                                                <span class="text-xs font-semibold text-slate-700">{{ $trx->approver->name }}</span>
                                            </div>
                                            <div class="text-[10px] text-slate-400 mb-0.5">
                                                {{ $isApproved ? 'Menyetujui' : 'Menolak' }}
                                            </div>
                                        @endif
                                        <div class="text-[11px] text-slate-400 font-medium">
                                            {{ \Carbon\Carbon::parse($trx->updated_at)->format('d M Y') }}
                                        </div>
                                        <div class="text-[10px] text-slate-300">
                                            {{ \Carbon\Carbon::parse($trx->updated_at)->format('H:i') }} WIB
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Pagination --}}
        @if($history->hasPages())
            <div class="mt-6 flex items-center justify-between flex-wrap gap-3 text-sm">
                <span class="text-slate-500 text-xs">
                    Menampilkan {{ $history->firstItem() }}–{{ $history->lastItem() }} dari {{ $history->total() }} riwayat
                </span>
                {{ $history->links() }}
            </div>
        @endif
    @endif

    {{-- Hidden Reject Form --}}
    <form id="rejectForm" method="POST" action="" class="hidden">
        @csrf
        <input type="hidden" name="reason" id="rejectReason">
    </form>

    <script>
        function rejectTransaction(id) {
            Swal.fire({
                title: 'Tolak Transaksi',
                text: 'Berikan alasan detail kenapa transaksi ini ditolak.',
                input: 'textarea',
                inputPlaceholder: 'Ketik alasan penolakan di sini...',
                showCancelButton: true,
                confirmButtonText: 'Tolak Pengajuan',
                confirmButtonColor: '#ef4444',
                cancelButtonText: 'Batal',
                inputAttributes: { style: 'min-height: 80px' },
                inputValidator: (value) => {
                    if (!value) return 'Alasan penolakan tidak boleh kosong!'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('rejectForm');
                    form.action = `/transactions/${id}/reject`;
                    document.getElementById('rejectReason').value = result.value;
                    form.submit();
                }
            });
        }
    </script>
@endsection