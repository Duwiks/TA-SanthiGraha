@extends('layouts.admin')

@section('title', 'Approval Transaksi - SanthiGraha')
@section('page_title', 'Approval Transaksi')

@section('content')

    {{-- ================================================================
    BAGIAN 1: ANTREAN PENDING
    ================================================================ --}}
    <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Antrean Menunggu Persetujuan</h2>
            <p class="text-sm text-slate-500 mt-1">Transaksi yang diajukan pegawai dan butuh tindakan Anda.</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @if($transactions->total() > 0)
                <div
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 text-sm font-bold">
                    <i class="ph ph-clock text-base"></i> {{ $transactions->total() }} menunggu
                </div>
            @endif
            {{-- Sort Antrean --}}
            <a href="{{ route('approvals.index', array_merge(request()->query(), ['sort' => 'latest'])) }}"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all
                      {{ request('sort', 'latest') === 'latest' ? 'bg-slate-700 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                <i class="ph ph-sort-descending"></i> Terbaru
            </a>
            <a href="{{ route('approvals.index', array_merge(request()->query(), ['sort' => 'oldest'])) }}"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all
                      {{ request('sort') === 'oldest' ? 'bg-slate-700 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                <i class="ph ph-sort-ascending"></i> Terlama
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mb-10">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="px-5 py-4">TANGGAL PENGAJUAN</th>
                        <th class="px-5 py-4">TANGGAL NOTA</th>
                        <th class="px-5 py-4">PROYEK & KATEGORI</th>
                        <th class="px-5 py-4">NOMINAL</th>
                        <th class="px-5 py-4">PENGAJU</th>
                        <th class="px-5 py-4 text-center">TINDAKAN</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $trx)
                        <tr class="hover:bg-slate-50 transition-colors">

                            {{-- Tanggal Pengajuan --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="font-medium text-slate-800">
                                    {{ \Carbon\Carbon::parse($trx->created_at)->format('d M Y') }}</div>
                            </td>

                            {{-- Tanggal Nota --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="font-medium text-slate-700">
                                    {{ \Carbon\Carbon::parse($trx->transaction_date)->format('d M Y') }}
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
                                @if($trx->receipt_photo)
                                    <div class="mt-2">
                                        @if(str_ends_with(strtolower($trx->receipt_photo), '.pdf'))
                                            <a href="{{ asset('storage/' . $trx->receipt_photo) }}" target="_blank"
                                                class="inline-flex items-center gap-1 text-[11px] font-semibold text-red-600 bg-red-50 px-2.5 py-1 rounded-lg hover:bg-red-100 transition-colors border border-red-100">
                                                <i class="ph ph-file-pdf"></i> Lihat PDF
                                            </a>
                                        @else
                                            <a href="{{ asset('storage/' . $trx->receipt_photo) }}" target="_blank">
                                                <img src="{{ asset('storage/' . $trx->receipt_photo) }}" alt="Bukti"
                                                    class="h-14 w-20 object-cover rounded-lg border border-slate-200 hover:opacity-80 transition-opacity">
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </td>

                            {{-- Nominal --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="font-bold {{ $trx->type === 'pemasukan' ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ $trx->type === 'pemasukan' ? '+' : '' }} Rp
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
                                    <span class="font-medium text-slate-700 text-sm">{{ $trx->user->name ?? '-' }}</span>
                                </div>
                            </td>

                            {{-- Tindakan --}}
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                <div class="flex flex-col items-center gap-2">
                                    {{-- Lihat Detail --}}
                                    <a href="{{ route('transactions.admin-show', [$trx->id, 'from' => 'approvals']) }}"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 text-xs font-semibold hover:bg-slate-200 transition-colors w-full justify-center">
                                        <i class="ph ph-eye text-sm"></i> Detail
                                    </a>
                                    <div class="flex items-center gap-2">
                                        <button type="button" onclick="handleApproveClick({{ $trx->id }}, {{ $trx->project_id }}, {{ $trx->category_id }}, '{{ $trx->payment_stage ?? 'proses' }}')"
                                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-500 text-white font-semibold text-xs hover:bg-emerald-600 hover:shadow-lg hover:shadow-emerald-500/20 transition-all">
                                            <i class="ph ph-check-circle text-sm"></i> Setujui
                                        </button>
                                        <button onclick="rejectTransaction({{ $trx->id }})"
                                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-red-50 text-red-600 font-semibold text-xs hover:bg-red-500 hover:text-white transition-all border border-red-100 hover:border-red-500">
                                            <i class="ph ph-x-circle text-sm"></i> Tolak
                                        </button>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
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
    BAGIAN 2: RIWAYAT PERSETUJUAN
    ================================================================ --}}
    <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Riwayat Persetujuan</h2>
            <p class="text-sm text-slate-500 mt-1">Log semua transaksi yang sudah diproses.</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            {{-- Sort Riwayat --}}
            <a href="{{ route('approvals.index', array_merge(request()->query(), ['sort' => 'latest'])) }}"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all
                      {{ request('sort', 'latest') === 'latest' ? 'bg-slate-700 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                <i class="ph ph-sort-descending"></i> Terbaru
            </a>
            <a href="{{ route('approvals.index', array_merge(request()->query(), ['sort' => 'oldest'])) }}"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all
                      {{ request('sort') === 'oldest' ? 'bg-slate-700 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                <i class="ph ph-sort-ascending"></i> Terlama
            </a>

            {{-- Filter Tab --}}
            <a href="{{ route('approvals.index', ['sort' => request('sort', 'latest')]) }}"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold transition-all
                      {{ !request('filter_status') ? 'bg-slate-700 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                <i class="ph ph-list-bullets"></i> Semua
                <span
                    class="ml-1 text-[11px] font-bold px-1.5 py-0.5 rounded-full {{ !request('filter_status') ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500' }}">
                    {{ $historyApprovedCount + $historyRejectedCount }}
                </span>
            </a>
            <a href="{{ route('approvals.index', ['filter_status' => 'approved', 'sort' => request('sort', 'latest')]) }}"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold transition-all
                      {{ request('filter_status') === 'approved' ? 'bg-emerald-500 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                <i class="ph ph-check-circle"></i> Disetujui
                <span
                    class="ml-1 text-[11px] font-bold px-1.5 py-0.5 rounded-full {{ request('filter_status') === 'approved' ? 'bg-white/20 text-white' : 'bg-emerald-50 text-emerald-600' }}">
                    {{ $historyApprovedCount }}
                </span>
            </a>
            <a href="{{ route('approvals.index', ['filter_status' => 'rejected', 'sort' => request('sort', 'latest')]) }}"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold transition-all
                      {{ request('filter_status') === 'rejected' ? 'bg-red-500 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                <i class="ph ph-x-circle"></i> Ditolak
                <span
                    class="ml-1 text-[11px] font-bold px-1.5 py-0.5 rounded-full {{ request('filter_status') === 'rejected' ? 'bg-white/20 text-white' : 'bg-red-50 text-red-600' }}">
                    {{ $historyRejectedCount }}
                </span>
            </a>
        </div>
    </div>

    {{-- Riwayat sebagai Tabel (konsisten dengan halaman lain) --}}
    @if($history->isEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 px-6 py-16 text-center">
            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="ph ph-clock-clockwise text-3xl text-slate-300"></i>
            </div>
            <p class="text-base font-bold text-slate-800">Belum Ada Riwayat</p>
            <p class="text-sm text-slate-500 mt-1">Riwayat akan muncul setelah Anda memproses transaksi.</p>
        </div>
    @else
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-4">TANGGAL PROSES</th>
                            <th class="px-5 py-4">TANGGAL NOTA</th>
                            <th class="px-5 py-4">PROYEK & KATEGORI</th>
                            <th class="px-5 py-4">NOMINAL</th>
                            <th class="px-5 py-4">PENGAJU</th>
                            <th class="px-5 py-4 text-center">STATUS</th>
                            <th class="px-5 py-4">CATATAN</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($history as $trx)
                            @php $isApproved = $trx->status === 'approved';
                            $lastRejection = $trx->rejections->last(); @endphp
                            <tr class="hover:bg-slate-50 transition-colors">

                                {{-- Tanggal Proses --}}
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="font-medium text-slate-800">
                                        {{ \Carbon\Carbon::parse($trx->updated_at)->format('d M Y') }}</div>
                                </td>

                                {{-- Tanggal Nota --}}
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="font-medium text-slate-700">
                                        {{ $trx->transaction_date ? \Carbon\Carbon::parse($trx->transaction_date)->format('d M Y') : '-' }}
                                    </div>
                                </td>

                                {{-- Proyek & Kategori --}}
                                <td class="px-5 py-4">
                                    <div class="font-medium text-slate-800">{{ $trx->project->project_name ?? '-' }}</div>
                                    <div class="text-xs text-slate-500 mt-0.5">{{ $trx->category->category_name ?? '-' }}</div>
                                    @php
                                        $desc = $trx->description;
                                        if ($trx->nota_merah_id) {
                                            $desc = Str::replaceFirst('[Nota Merah] ', '', $desc);
                                            $desc = preg_replace('/^\[Nota Merah #\d+\]$/', '', $desc ?? '');
                                            $desc = trim($desc);
                                        }
                                    @endphp
                                    @if($desc)
                                        <div class="text-[12px] text-slate-400 mt-1 italic">{{ Str::limit($desc, 45) }}
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
                                        {{ $trx->type === 'pemasukan' ? '+' : '' }} Rp
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
                                                class="text-[10px] uppercase font-medium text-slate-400">{{ $trx->payment_method }}</span>
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
                                                <div class="text-xs text-slate-400">{{ $isApproved ? '✓' : '✗' }}
                                                    {{ $trx->approver->name }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- Status --}}
                                <td class="px-5 py-4 text-center whitespace-nowrap">
                                    @if($isApproved)
                                        <span
                                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">
                                            <i class="ph ph-check-circle"></i> Disetujui
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">
                                            <i class="ph ph-x-circle"></i> Ditolak
                                        </span>
                                    @endif
                                </td>

                                {{-- Catatan / Bukti --}}
                                <td class="px-5 py-4">
                                    @if(!$isApproved && $lastRejection)
                                        <div class="text-xs text-red-600 leading-relaxed max-w-[180px]">
                                            <i class="ph ph-warning text-red-400"></i>
                                            {{ Str::limit($lastRejection->reason, 70) }}
                                        </div>
                                    @elseif($trx->receipt_photo)
                                        @if(str_ends_with(strtolower($trx->receipt_photo), '.pdf'))
                                            <a href="{{ asset('storage/' . $trx->receipt_photo) }}" target="_blank"
                                                class="inline-flex items-center gap-1 text-[11px] font-semibold text-red-600 bg-red-50 px-2.5 py-1 rounded-lg hover:bg-red-100 border border-red-100">
                                                <i class="ph ph-file-pdf"></i> PDF
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

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($history->hasPages())
                <div class="px-6 py-4 border-t border-slate-100 text-sm flex items-center justify-between flex-wrap gap-3">
                    <span class="text-slate-500 text-xs">
                        Menampilkan {{ $history->firstItem() }}–{{ $history->lastItem() }} dari {{ $history->total() }} riwayat
                    </span>
                    {{ $history->links() }}
                </div>
            @endif
        </div>
    @endif

    {{-- Hidden Reject Form --}}
    <form id="rejectForm" method="POST" action="" class="hidden">
        @csrf
        <input type="hidden" name="reason" id="rejectReason">
    </form>

    {{-- Hidden Approve Form --}}
    <form id="directApproveForm" method="POST" action="" class="hidden">
        @csrf
        <input type="hidden" name="payment_stage" id="directPaymentStage">
        <input type="hidden" name="payment_group_action" id="directGroupAction">
        <input type="hidden" name="payment_group_label" id="directGroupLabel">
    </form>

    {{-- Modal Konfirmasi Approval Payment Group Selesai --}}
    <div id="approvalGroupModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm hidden p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 relative animate-in fade-in zoom-in duration-200">
            {{-- Icon & Judul --}}
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                    <i class="ph ph-warning-circle text-2xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-base">Kelompok Sebelumnya Selesai</h3>
                    <p class="text-xs text-slate-500">Proyek & kategori transaksi ini sebelumnya sudah selesai.</p>
                </div>
            </div>

            {{-- Info Group Sebelumnya --}}
            <div class="bg-slate-50 rounded-xl p-4 mb-4 text-xs space-y-1.5 border border-slate-100">
                <div class="flex justify-between">
                    <span class="text-slate-500">Proyek:</span>
                    <span class="font-semibold text-slate-700" id="modal_app_project_name">-</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Kategori:</span>
                    <span class="font-semibold text-slate-700" id="modal_app_category_name">-</span>
                </div>
                <div class="flex justify-between" id="modal_app_label_row">
                    <span class="text-slate-500">Label Sebelumnya:</span>
                    <span class="font-semibold text-slate-700" id="modal_app_label">-</span>
                </div>
                <div class="flex justify-between border-t border-slate-200 pt-1.5 mt-1.5">
                    <span class="text-slate-500">Total Sebelumnya:</span>
                    <span class="font-bold text-emerald-600" id="modal_app_total_amount">-</span>
                </div>
            </div>

            {{-- Pertanyaan / Pilihan --}}
            <div id="modalAppChoiceSection" class="mb-4">
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Tindakan Kelompok Pembayaran:</label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex items-center gap-2 p-3 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition-colors has-[:checked]:border-brand-500 has-[:checked]:bg-indigo-50/40">
                        <input type="radio" name="modal_group_action" value="lanjutkan" checked onchange="toggleAppNewGroupLabel(this.value)" class="text-brand-500">
                        <span class="text-xs font-medium text-slate-700">Lanjutkan Kelompok Lama</span>
                    </label>
                    <label class="flex items-center gap-2 p-3 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition-colors has-[:checked]:border-brand-500 has-[:checked]:bg-indigo-50/40">
                        <input type="radio" name="modal_group_action" value="baru" onchange="toggleAppNewGroupLabel(this.value)" class="text-brand-500">
                        <span class="text-xs font-medium text-slate-700">Buat Kelompok Baru</span>
                    </label>
                </div>
            </div>

            {{-- Input Label Baru (hidden default) --}}
            <div id="appNewGroupLabelSection" class="hidden mb-4">
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    Label Kelompok Baru <span class="text-red-500">*</span>
                </label>
                <input type="text" id="modal_app_new_label" placeholder="Contoh: Tahap 2, Perbaikan Lanjutan"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-400 outline-none">
                <p class="text-[11px] text-red-500 mt-1 hidden" id="modal_app_label_error">Label kelompok baru wajib diisi.</p>
            </div>

            {{-- Konfirmasi Status Pembayaran --}}
            <div class="mb-5">
                <label for="modal_app_stage" class="block text-xs font-semibold text-slate-700 mb-1">
                    Status Pembayaran Transaksi Ini <span class="text-red-500">*</span>
                </label>
                <select id="modal_app_stage" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-400 outline-none">
                    <option value="uang_muka">Uang Muka</option>
                    <option value="proses" selected>Proses</option>
                    <option value="selesai">Selesai</option>
                </select>
            </div>

            {{-- Tombol Aksi --}}
            <div class="flex gap-2">
                <button type="button" onclick="submitApproveModal()"
                    class="flex-1 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-semibold text-xs shadow-md shadow-emerald-500/20 transition-all flex items-center justify-center gap-1.5">
                    <i class="ph ph-check-circle"></i> Konfirmasi & Setujui
                </button>
                <button type="button" onclick="closeApprovalGroupModal()"
                    class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-600 text-xs font-semibold hover:bg-slate-200 transition-colors">
                    Batal
                </button>
            </div>

            <button type="button" onclick="closeApprovalGroupModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
                <i class="ph ph-x text-xl"></i>
            </button>
        </div>
    </div>

    <script>
        let currentApproveTrxId = null;

        function handleApproveClick(trxId, projectId, categoryId, currentStage) {
            currentApproveTrxId = trxId;

            // Cek status kelompok pembayaran via AJAX
            fetch(`{{ route('transactions.check-payment-group') }}?project_id=${projectId}&category_id=${categoryId}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.needs_confirmation) {
                    // Kelompok sebelumnya selesai → buka pop-up konfirmasi
                    openApprovalGroupModal(data.group, currentStage);
                } else {
                    // Kelompok belum selesai atau baru pertama kali → langsung setujui
                    directApprove(trxId, currentStage);
                }
            })
            .catch(() => {
                directApprove(trxId, currentStage);
            });
        }

        function directApprove(trxId, stage) {
            const form = document.getElementById('directApproveForm');
            form.action = `/transactions/${trxId}/approve`;
            document.getElementById('directPaymentStage').value = stage || 'proses';
            document.getElementById('directGroupAction').value = '';
            document.getElementById('directGroupLabel').value = '';
            form.submit();
        }

        function openApprovalGroupModal(group, currentStage) {
            document.getElementById('modal_app_project_name').textContent  = group.project_name;
            document.getElementById('modal_app_category_name').textContent = group.category_name;
            document.getElementById('modal_app_total_amount').textContent  =
                'Rp ' + Number(group.total_amount).toLocaleString('id-ID', {minimumFractionDigits: 0});

            const labelRow = document.getElementById('modal_app_label_row');
            if (group.label) {
                document.getElementById('modal_app_label').textContent = group.label;
                labelRow.classList.remove('hidden');
            } else {
                labelRow.classList.add('hidden');
            }

            document.querySelector('input[name="modal_group_action"][value="lanjutkan"]').checked = true;
            toggleAppNewGroupLabel('lanjutkan');
            if (currentStage === 'selesai') {
                document.getElementById('modal_app_stage').value = 'selesai';
            }
            document.getElementById('modal_app_new_label').value = '';
            document.getElementById('modal_app_label_error').classList.add('hidden');

            document.getElementById('approvalGroupModal').classList.remove('hidden');
        }

        function closeApprovalGroupModal() {
            document.getElementById('approvalGroupModal').classList.add('hidden');
            currentApproveTrxId = null;
        }

        function toggleAppNewGroupLabel(action) {
            const section = document.getElementById('appNewGroupLabelSection');
            const stageSelect = document.getElementById('modal_app_stage');

            if (action === 'baru') {
                section.classList.remove('hidden');
                stageSelect.innerHTML = `
                    <option value="uang_muka" selected>Uang Muka</option>
                    <option value="selesai">Selesai</option>
                `;
            } else {
                section.classList.add('hidden');
                stageSelect.innerHTML = `
                    <option value="proses" selected>Proses</option>
                    <option value="selesai">Selesai</option>
                `;
            }
        }

        function submitApproveModal() {
            if (!currentApproveTrxId) return;

            const action = document.querySelector('input[name="modal_group_action"]:checked').value;
            const stage = document.getElementById('modal_app_stage').value;
            let label = '';

            if (action === 'baru') {
                label = document.getElementById('modal_app_new_label').value.trim();
                if (!label) {
                    document.getElementById('modal_app_label_error').classList.remove('hidden');
                    return;
                }
            }

            const form = document.getElementById('directApproveForm');
            form.action = `/transactions/${currentApproveTrxId}/approve`;
            document.getElementById('directPaymentStage').value = stage;
            document.getElementById('directGroupAction').value = action;
            document.getElementById('directGroupLabel').value = label;
            form.submit();
        }

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