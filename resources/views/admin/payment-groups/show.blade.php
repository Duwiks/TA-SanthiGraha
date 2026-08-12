@extends('layouts.admin')

@section('title', 'Detail Kelompok Pembayaran - SanthiGraha')
@section('page_title', 'Detail Kelompok Pembayaran')

@section('content')

{{-- Header Breadcrumb --}}
<div class="mb-6 flex items-center justify-between flex-wrap gap-3">
    <a href="{{ route('payment-groups.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-indigo-600 transition-colors">
        <i class="ph ph-arrow-left"></i> Kembali ke Daftar Kelompok
    </a>
    <form action="{{ route('payment-groups.destroy', $group->id) }}" method="POST"
          onsubmit="return confirm('Apakah Anda yakin ingin menghapus kelompok pembayaran ini beserta seluruh transaksi di dalamnya?');">
        @csrf
        @method('DELETE')
        <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-red-50 text-red-600 text-xs font-semibold hover:bg-red-100 transition-colors border border-red-200">
            <i class="ph ph-trash"></i> Hapus Kelompok
        </button>
    </form>
</div>

@php
    $statusColor = match($group->payment_status) {
        'uang_muka' => 'bg-blue-100 text-blue-700 border-blue-200',
        'proses'    => 'bg-amber-100 text-amber-700 border-amber-200',
        'selesai'   => 'bg-emerald-100 text-emerald-700 border-emerald-200',
        default     => 'bg-slate-100 text-slate-600 border-slate-200',
    };
    $statusLabel = match($group->payment_status) {
        'uang_muka' => 'Uang Muka',
        'proses'    => 'Proses',
        'selesai'   => 'Selesai',
        default     => $group->payment_status,
    };
    $totalApproved = $group->transactions->where('status', 'approved')->sum('amount');
    $totalPending  = $group->transactions->where('status', 'pending')->sum('amount');
@endphp

{{-- Info Card --}}
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center gap-4 mb-4">
        <div class="w-12 h-12 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-500 shrink-0">
            <i class="ph ph-stack text-2xl"></i>
        </div>
        <div class="flex-1">
            <div class="flex flex-wrap items-center gap-2 mb-1">
                <h2 class="text-lg font-bold text-slate-800">
                    {{ $group->project->project_name ?? '-' }} — {{ $group->category->category_name ?? '-' }}
                </h2>
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $statusColor }}">
                    {{ $statusLabel }}
                </span>
            </div>
            @if($group->label)
                <p class="text-sm text-slate-500 italic">Label: <strong class="text-slate-700">{{ $group->label }}</strong></p>
            @endif
            <p class="text-xs text-slate-400 mt-1">Dibuat: {{ $group->created_at->format('d M Y, H:i') }}</p>
        </div>
    </div>

    {{-- Summary Amounts --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-2">
        <div class="bg-emerald-50 rounded-xl p-4">
            <p class="text-xs font-semibold text-emerald-600 mb-1 uppercase tracking-wider">Total Disetujui</p>
            <p class="text-lg font-bold text-emerald-700">Rp {{ number_format($totalApproved, 2, ',', '.') }}</p>
        </div>
        <div class="bg-amber-50 rounded-xl p-4">
            <p class="text-xs font-semibold text-amber-600 mb-1 uppercase tracking-wider">Menunggu Persetujuan</p>
            <p class="text-lg font-bold text-amber-700">Rp {{ number_format($totalPending, 2, ',', '.') }}</p>
        </div>
        <div class="bg-slate-50 rounded-xl p-4">
            <p class="text-xs font-semibold text-slate-500 mb-1 uppercase tracking-wider">Jumlah Transaksi</p>
            <p class="text-lg font-bold text-slate-700">{{ $group->transactions->count() }} transaksi</p>
        </div>
    </div>
</div>

{{-- Riwayat Transaksi dalam Group --}}
<div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
        <div class="w-8 h-8 rounded-full bg-slate-50 flex items-center justify-center text-slate-500">
            <i class="ph ph-clock-clockwise text-base"></i>
        </div>
        <h3 class="text-sm font-bold text-slate-700">Riwayat Transaksi dalam Kelompok Ini</h3>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-slate-600">
            <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100 text-xs uppercase tracking-wider">
                <tr>
                    <th class="px-6 py-3 w-10 text-center">NO</th>
                    <th class="px-6 py-3">TANGGAL</th>
                    <th class="px-6 py-3">PEGAWAI</th>
                    <th class="px-6 py-3 text-center">TAHAP</th>
                    <th class="px-6 py-3 text-right">NOMINAL</th>
                    <th class="px-6 py-3 text-center">BUKTI</th>
                    <th class="px-6 py-3 text-center">STATUS APPROVAL</th>
                    <th class="px-6 py-3 text-center">AKSI</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse($group->transactions as $index => $trx)
                    @php
                        $stageColor = match($trx->payment_stage) {
                            'uang_muka' => 'bg-blue-100 text-blue-700',
                            'proses'    => 'bg-amber-100 text-amber-700',
                            'selesai'   => 'bg-emerald-100 text-emerald-700',
                            default     => 'bg-slate-100 text-slate-500',
                        };
                        $stageLabel = match($trx->payment_stage) {
                            'uang_muka' => 'Uang Muka',
                            'proses'    => 'Proses',
                            'selesai'   => 'Selesai',
                            default     => '-',
                        };
                        $approvalColor = match($trx->status) {
                            'approved' => 'bg-emerald-100 text-emerald-700',
                            'pending'  => 'bg-amber-100 text-amber-700',
                            'rejected' => 'bg-red-100 text-red-700',
                            default    => 'bg-slate-100 text-slate-500',
                        };
                        $approvalLabel = match($trx->status) {
                            'approved' => 'Disetujui',
                            'pending'  => 'Menunggu',
                            'rejected' => 'Ditolak',
                            default    => $trx->status,
                        };
                    @endphp
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 text-center text-slate-400 text-xs">{{ $index + 1 }}</td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            {{ \Carbon\Carbon::parse($trx->transaction_date)->format('d/m/Y') }}
                        </td>
                        <td class="px-6 py-4 font-medium text-slate-800">
                            {{ $trx->user->name ?? '-' }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($trx->payment_stage)
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $stageColor }}">
                                    {{ $stageLabel }}
                                </span>
                            @else
                                <span class="text-slate-300 text-xs">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-right font-bold text-slate-700 whitespace-nowrap">
                            Rp {{ number_format($trx->amount, 2, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 text-center">
                            @if($trx->receipt_photo)
                                @if(str_ends_with(strtolower($trx->receipt_photo), '.pdf'))
                                    <a href="{{ asset('storage/' . $trx->receipt_photo) }}" target="_blank"
                                        class="inline-flex items-center gap-1 text-[11px] font-semibold text-red-600 bg-red-50 px-2 py-1 rounded-lg hover:bg-red-100 transition-colors border border-red-100">
                                        <i class="ph ph-file-pdf"></i> PDF
                                    </a>
                                @else
                                    <a href="{{ asset('storage/' . $trx->receipt_photo) }}" target="_blank">
                                        <img src="{{ asset('storage/' . $trx->receipt_photo) }}" alt="Bukti"
                                            class="h-9 w-12 object-cover rounded-lg border border-slate-200 hover:opacity-80 transition-opacity mx-auto">
                                    </a>
                                @endif
                            @else
                                <span class="text-xs text-slate-300">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-bold {{ $approvalColor }}">
                                {{ $approvalLabel }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-center whitespace-nowrap">
                            <div class="flex items-center justify-center gap-1.5">
                                <a href="{{ route('transactions.admin-show', $trx->id) }}"
                                   class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-slate-50 text-slate-600 text-xs font-semibold hover:bg-slate-100 transition-colors border border-slate-200"
                                   title="Lihat Detail">
                                    <i class="ph ph-eye"></i> Detail
                                </a>
                                <form action="{{ route('transactions.destroy', $trx->id) }}" method="POST"
                                      id="delete-trx-form-{{ $trx->id }}" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button"
                                            onclick="confirmDelete('delete-trx-form-{{ $trx->id }}', 'Apakah Anda yakin ingin menghapus transaksi #{{ $trx->id }} dari kelompok pembayaran ini?')"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-red-50 text-red-600 text-xs font-semibold hover:bg-red-100 transition-colors border border-red-200"
                                            title="Hapus Transaksi">
                                        <i class="ph ph-trash"></i> Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center">
                            <i class="ph ph-receipt text-4xl text-slate-200 mb-3"></i>
                            <p class="text-sm text-slate-400">Belum ada transaksi dalam kelompok ini.</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            @if($group->transactions->count() > 0)
            <tfoot>
                <tr class="bg-emerald-50 border-t-2 border-slate-200">
                    <td colspan="4" class="px-6 py-3 text-right font-bold text-slate-600 text-xs uppercase tracking-wider">Total Disetujui</td>
                    <td class="px-6 py-3 text-right font-bold text-emerald-700 whitespace-nowrap">
                        Rp {{ number_format($totalApproved, 2, ',', '.') }}
                    </td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>

@endsection
