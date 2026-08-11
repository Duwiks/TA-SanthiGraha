@extends('layouts.pegawai')

@section('title', 'Detail Transaksi #' . $transaction->id . ' - SanthiGraha')
@section('page_title', 'Detail Transaksi')

@section('content')
    <div class="max-w-4xl mx-auto">

        {{-- Back + Header --}}
        <div class="mb-6">
            <a href="{{ route('transactions.index') }}"
                class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-emerald-600 transition-colors mb-4">
                <i class="ph ph-arrow-left"></i> Kembali ke Riwayat Transaksi
            </a>
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h2 class="text-lg font-bold text-slate-800">
                        {{ $paymentGroup ? 'Transaksi Kelompok #' . $paymentGroup->id . ' (' . ($paymentGroup->project->project_name ?? '') . ')' : 'Transaksi #' . $transaction->id }}
                    </h2>
                    <p class="text-sm text-slate-500 mt-0.5">
                        {{ $paymentGroup ? 'Akumulasi ' . $groupTransactions->count() . ' nota untuk ' . ($paymentGroup->category->category_name ?? '-') : 'Diajukan pada ' . $transaction->created_at->format('d M Y, H:i') . ' WITA' }}
                    </p>
                </div>
                {{-- Badge Status --}}
                @php
                    $statusConfig = [
                        'pending'  => ['color' => 'bg-amber-100 text-amber-700',    'icon' => 'ph-clock',        'label' => 'Menunggu Persetujuan'],
                        'approved' => ['color' => 'bg-emerald-100 text-emerald-700', 'icon' => 'ph-check-circle', 'label' => 'Disetujui'],
                        'rejected' => ['color' => 'bg-red-100 text-red-700',         'icon' => 'ph-x-circle',     'label' => 'Ditolak'],
                    ];
                    $sc = $statusConfig[$transaction->status] ?? [
                        'color' => 'bg-slate-100 text-slate-600',
                        'icon'  => 'ph-question',
                        'label' => $transaction->status,
                    ];
                @endphp
                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold {{ $sc['color'] }}">
                    <i class="ph {{ $sc['icon'] }} text-base"></i>
                    {{ $sc['label'] }}
                </span>
            </div>
        </div>

        {{-- Ringkasan Utama Transaksi --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-6">

            {{-- Informasi Pengajuan --}}
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
                <h3 class="text-sm font-bold text-slate-700 mb-4 flex items-center gap-2">
                    <i class="ph ph-receipt text-emerald-500"></i> Detail Pengajuan
                </h3>
                <dl class="space-y-3 text-sm">

                    <div class="flex justify-between">
                        <dt class="text-slate-500">Proyek</dt>
                        <dd class="font-semibold text-slate-800 text-right">{{ $transaction->project->project_name ?? '-' }}</dd>
                    </div>

                    <div class="flex justify-between">
                        <dt class="text-slate-500">Kategori</dt>
                        <dd class="font-semibold text-slate-800">{{ $transaction->category->category_name ?? '-' }}</dd>
                    </div>

                    <div class="flex justify-between">
                        <dt class="text-slate-500">Tanggal Nota</dt>
                        <dd class="font-semibold text-slate-800">
                            {{ \Carbon\Carbon::parse($transaction->transaction_date)->format('d M Y') }}
                            @if($paymentGroup && $groupTransactions->count() > 1)
                                <span class="text-xs text-slate-400 font-normal">(Terakhir)</span>
                            @endif
                        </dd>
                    </div>

                    <div class="flex justify-between">
                        <dt class="text-slate-500">Tipe</dt>
                        <dd>
                            <span class="inline-flex items-center gap-1 text-xs font-bold px-2.5 py-1 rounded-full
                                {{ $transaction->type === 'pemasukan' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">
                                <i class="ph {{ $transaction->type === 'pemasukan' ? 'ph-trend-up' : 'ph-trend-down' }}"></i>
                                {{ ucfirst($transaction->type) }}
                            </span>
                        </dd>
                    </div>

                    {{-- Nominal Total Akumulasi --}}
                    <div class="flex justify-between items-start pt-1">
                        <dt class="text-slate-500">
                            {{ $paymentGroup && $groupTransactions->count() > 1 ? 'Total Nominal (Akumulasi)' : 'Nominal' }}
                        </dt>
                        <dd class="text-right">
                            <span class="font-bold text-lg {{ $transaction->type === 'pemasukan' ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ $transaction->type === 'pemasukan' ? '+' : '' }} Rp {{ number_format($totalGroupAmount, 2, ',', '.') }}
                            </span>
                            @if($paymentGroup && $groupTransactions->count() > 1)
                                <div class="text-[11px] text-indigo-600 font-medium mt-0.5">
                                    Hasil penjumlahan {{ $groupTransactions->count() }} nota
                                </div>
                            @endif
                        </dd>
                    </div>

                    <div class="flex justify-between">
                        <dt class="text-slate-500">Metode Bayar</dt>
                        <dd class="font-semibold text-slate-800">{{ $transaction->payment_method ?? '-' }}</dd>
                    </div>

                    <div class="flex justify-between items-center">
                        <dt class="text-slate-500">Status Pembayaran</dt>
                        <dd>
                            @php
                                $stage = $paymentGroup ? $paymentGroup->payment_status : $transaction->payment_stage;
                                $stageColor = match($stage) {
                                    'uang_muka' => 'bg-blue-100 text-blue-700 border-blue-200',
                                    'proses'    => 'bg-amber-100 text-amber-700 border-amber-200',
                                    'selesai'   => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                    default     => 'bg-slate-100 text-slate-600 border-slate-200',
                                };
                                $stageLabel = match($stage) {
                                    'uang_muka' => 'Uang Muka',
                                    'proses'    => 'Proses',
                                    'selesai'   => 'Selesai',
                                    default     => 'Menunggu Validasi Admin',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $stageColor }}">
                                {{ $stageLabel }}
                            </span>
                        </dd>
                    </div>

                    @if(!$paymentGroup || $groupTransactions->count() <= 1)
                        <div class="pt-2 border-t border-slate-100">
                            <dt class="text-slate-500 mb-1">Keterangan</dt>
                            <dd class="text-slate-700 leading-relaxed {{ !$transaction->description ? 'text-slate-400 italic' : '' }}">
                                @php
                                    $desc = $transaction->description;
                                    if ($transaction->nota_merah_id) {
                                        $desc = Str::replaceFirst('[Nota Merah] ', '', $desc ?? '');
                                        $desc = preg_replace('/^\[Nota Merah #\d+\]$/', '', $desc ?? '');
                                        $desc = trim($desc);
                                    }
                                @endphp
                                {{ $desc ?: 'Tidak ada keterangan' }}
                            </dd>
                        </div>
                    @endif

                </dl>
            </div>

            {{-- Kolom kanan: Status & Catatan Approval --}}
            <div class="space-y-4">

                {{-- Status Card --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                    <h3 class="text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                        <i class="ph ph-info text-indigo-500"></i> Status Pengajuan
                    </h3>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-slate-500">Status Saat Ini</span>
                            <span class="font-bold {{ $transaction->status === 'approved' ? 'text-emerald-600' : ($transaction->status === 'rejected' ? 'text-red-600' : 'text-amber-600') }}">
                                {{ $sc['label'] }}
                            </span>
                        </div>
                        @if($transaction->approver)
                            <div class="flex items-center justify-between text-sm pt-2 border-t border-slate-100">
                                <span class="text-slate-500">{{ $transaction->status === 'approved' ? 'Disetujui oleh' : 'Diproses oleh' }}</span>
                                <span class="font-semibold text-slate-700">{{ $transaction->approver->name }}</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Badge Nota Merah --}}
                @if($transaction->nota_merah_id)
                    <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <i class="ph ph-note-pencil text-red-500 text-lg flex-shrink-0"></i>
                            <div>
                                <p class="text-sm font-bold text-red-700 mb-0.5">Berasal dari Nota Merah</p>
                                <p class="text-xs text-red-500">Transaksi ini dibuat otomatis dari konfirmasi nota merah.</p>
                                <a href="{{ route('nota-merah.show', $transaction->nota_merah_id) }}"
                                    class="inline-flex items-center gap-1 text-xs font-semibold text-red-700 underline mt-1 hover:text-red-900 transition-colors">
                                    <i class="ph ph-arrow-square-out"></i> Lihat Nota Merah #{{ $transaction->nota_merah_id }}
                                </a>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Aksi Edit / Hapus jika masih Pending / Rejected --}}
                @if(in_array($transaction->status, ['pending', 'rejected']))
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 flex gap-2">
                        <a href="{{ route('transactions.edit', $transaction->id) }}"
                            class="flex-1 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-semibold text-xs text-center flex items-center justify-center gap-1.5 transition-colors shadow-sm">
                            <i class="ph ph-pencil-simple text-sm"></i> Edit & Ajukan Ulang
                        </a>
                        <form action="{{ route('transactions.destroy', $transaction->id) }}" method="POST" id="deleteFormPegawai" class="inline">
                            @csrf @method('DELETE')
                            <button type="button" onclick="confirmDelete('deleteFormPegawai', 'Apakah Anda yakin ingin menghapus transaksi ini?')"
                                class="px-4 py-2.5 rounded-xl bg-red-50 hover:bg-red-500 text-red-600 hover:text-white font-semibold text-xs transition-colors border border-red-100">
                                <i class="ph ph-trash text-sm"></i>
                            </button>
                        </form>
                    </div>
                @endif

            </div>
        </div>

        {{-- ======================================================= --}}
        {{-- SECTION: DAFTAR SEMUA NOTA & BUKTI TRANSAKSI TERKAIT --}}
        {{-- ======================================================= --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden mb-6">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between flex-wrap gap-2">
                <div class="flex items-center gap-2.5">
                    <div class="w-8 h-8 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-600 font-bold">
                        <i class="ph ph-images text-lg"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm">
                            {{ $groupTransactions->count() > 1 ? 'Rincian Semua Nota Terkait (' . $groupTransactions->count() . ' Nota)' : 'Bukti & Rincian Nota' }}
                        </h3>
                        <p class="text-xs text-slate-400">Daftar seluruh nota fisik dan nominal rincian dalam transaksi ini</p>
                    </div>
                </div>
                <span class="text-xs font-bold px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100">
                    Total: Rp {{ number_format($totalGroupAmount, 2, ',', '.') }}
                </span>
            </div>

            <div class="divide-y divide-slate-100">
                @foreach($groupTransactions as $index => $trxItem)
                    @php
                        $itemStageColor = match($trxItem->payment_stage) {
                            'uang_muka' => 'bg-blue-100 text-blue-700 border-blue-200',
                            'proses'    => 'bg-amber-100 text-amber-700 border-amber-200',
                            'selesai'   => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                            default     => 'bg-slate-100 text-slate-600 border-slate-200',
                        };
                        $itemStageLabel = match($trxItem->payment_stage) {
                            'uang_muka' => 'Uang Muka',
                            'proses'    => 'Proses',
                            'selesai'   => 'Selesai',
                            default     => $trxItem->payment_stage ?: '-',
                        };
                    @endphp

                    <div class="p-6 hover:bg-slate-50/50 transition-colors">
                        <div class="flex flex-col lg:flex-row gap-5 items-start">
                            
                            {{-- Bukti Struk / Foto Nota (Kiri) --}}
                            <div class="w-full lg:w-48 shrink-0">
                                @if($trxItem->receipt_photo)
                                    @if(str_ends_with(strtolower($trxItem->receipt_photo), '.pdf'))
                                        <div class="bg-red-50 border border-red-100 rounded-xl p-4 text-center">
                                            <i class="ph ph-file-pdf text-4xl text-red-500 mb-1"></i>
                                            <p class="text-xs font-bold text-red-700 mb-2">Dokumen PDF</p>
                                            <a href="{{ asset('storage/' . $trxItem->receipt_photo) }}" target="_blank"
                                                class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-red-600 hover:bg-red-700 text-white text-xs font-semibold shadow-sm transition-colors">
                                                <i class="ph ph-arrow-square-out"></i> Buka PDF
                                            </a>
                                        </div>
                                    @else
                                        <a href="{{ asset('storage/' . $trxItem->receipt_photo) }}" target="_blank" class="block group relative overflow-hidden rounded-xl border border-slate-200 shadow-sm">
                                            <img src="{{ asset('storage/' . $trxItem->receipt_photo) }}" alt="Nota #{{ $trxItem->id }}"
                                                class="w-full h-32 object-cover group-hover:scale-105 transition-transform duration-200">
                                            <div class="absolute inset-0 bg-black/30 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center text-white text-xs font-semibold gap-1">
                                                <i class="ph ph-magnifying-glass-plus text-base"></i> Lihat Penuh
                                            </div>
                                        </a>
                                    @endif
                                @else
                                    <div class="h-32 bg-slate-100 rounded-xl border border-dashed border-slate-200 flex flex-col items-center justify-center text-slate-400 text-xs">
                                        <i class="ph ph-image-broken text-3xl mb-1"></i>
                                        <span>Tidak ada foto nota</span>
                                    </div>
                                @endif
                            </div>

                            {{-- Rincian Data Nota (Kanan) --}}
                            <div class="flex-1 min-w-0 space-y-2">
                                <div class="flex items-center justify-between flex-wrap gap-2">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2.5 py-0.5 rounded-md bg-slate-800 text-white font-bold text-xs">
                                            Nota #{{ $index + 1 }}
                                        </span>
                                        <span class="text-xs text-slate-400">ID Transaksi #{{ $trxItem->id }}</span>
                                    </div>
                                    <span class="text-base font-bold text-slate-800">
                                        Rp {{ number_format($trxItem->amount, 2, ',', '.') }}
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs pt-1">
                                    <div>
                                        <span class="text-slate-400 block mb-0.5">Tanggal Nota:</span>
                                        <span class="font-semibold text-slate-700">
                                            {{ \Carbon\Carbon::parse($trxItem->transaction_date)->format('d M Y') }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 block mb-0.5">Tahap Pembayaran:</span>
                                        <span class="inline-flex items-center px-2 py-0.5 rounded text-[11px] font-bold border {{ $itemStageColor }}">
                                            {{ $itemStageLabel }}
                                        </span>
                                    </div>
                                    <div>
                                        <span class="text-slate-400 block mb-0.5">Metode Bayar:</span>
                                        <span class="font-semibold text-slate-700">{{ $trxItem->payment_method ?? '-' }}</span>
                                    </div>
                                </div>

                                @if($trxItem->description)
                                    <div class="bg-slate-50 rounded-xl p-3 text-xs text-slate-600 mt-2">
                                        <span class="font-semibold text-slate-700 block mb-0.5">Keterangan Nota:</span>
                                        {{ $trxItem->description }}
                                    </div>
                                @endif

                                <div class="text-[11px] text-slate-400 flex items-center gap-4 pt-1">
                                    <span>Diajukan oleh: <strong class="text-slate-600">{{ $trxItem->user->name ?? '-' }}</strong></span>
                                    @if($trxItem->approver)
                                        <span>Disetujui oleh: <strong class="text-slate-600">{{ $trxItem->approver->name }}</strong></span>
                                    @endif
                                </div>
                            </div>

                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>
@endsection
