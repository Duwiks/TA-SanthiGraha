@extends('layouts.pegawai')

@section('title', 'Detail Transaksi #' . $transaction->id . ' - SanthiGraha')
@section('page_title', 'Detail Transaksi')

@section('content')
    <div class="max-w-3xl mx-auto">

        {{-- Back + Header --}}
        <div class="mb-6">
            <a href="{{ route('transactions.index') }}"
                class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-emerald-600 transition-colors mb-4">
                <i class="ph ph-arrow-left"></i> Kembali ke Riwayat Transaksi
            </a>
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Transaksi #{{ $transaction->id }}</h2>
                    <p class="text-sm text-slate-500 mt-0.5">
                        Diajukan pada {{ $transaction->created_at->format('d M Y, H:i') }} WITA
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

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

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

                    <div class="flex justify-between">
                        <dt class="text-slate-500">Nominal</dt>
                        <dd class="font-bold text-base {{ $transaction->type === 'pemasukan' ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ $transaction->type === 'pemasukan' ? '+' : '' }} Rp {{ number_format($transaction->amount, 2, ',', '.') }}
                        </dd>
                    </div>

                    <div class="flex justify-between">
                        <dt class="text-slate-500">Metode Bayar</dt>
                        <dd class="font-semibold text-slate-800">{{ $transaction->payment_method ?? '-' }}</dd>
                    </div>

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

                </dl>
            </div>

            {{-- Kolom kanan: Bukti + Status Info --}}
            <div class="space-y-4">

                {{-- Foto Bukti Transaksi --}}
                @if($transaction->receipt_photo)
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                        <h3 class="text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                            <i class="ph ph-image text-amber-500"></i> Foto Bukti Transaksi
                        </h3>
                        @if(str_ends_with(strtolower($transaction->receipt_photo), '.pdf'))
                            <a href="{{ asset('storage/' . $transaction->receipt_photo) }}" target="_blank"
                                class="flex items-center gap-2 px-4 py-3 rounded-xl bg-red-50 text-red-600 font-medium text-sm hover:bg-red-100 transition-colors border border-red-100">
                                <i class="ph ph-file-pdf text-xl"></i> Lihat Dokumen PDF
                            </a>
                        @else
                            <a href="{{ asset('storage/' . $transaction->receipt_photo) }}" target="_blank">
                                <img src="{{ asset('storage/' . $transaction->receipt_photo) }}" alt="Bukti Transaksi"
                                    class="w-full h-40 object-cover rounded-xl border border-slate-200 hover:opacity-80 transition-opacity">
                            </a>
                        @endif
                    </div>
                @endif

                {{-- Info Admin Penyetuju (hanya jika approved) --}}
                @if($transaction->status === 'approved' && $transaction->approver)
                    <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-emerald-200 text-emerald-700 flex items-center justify-center font-bold text-sm shrink-0">
                                {{ substr($transaction->approver->name, 0, 1) }}
                            </div>
                            <div>
                                <p class="text-xs text-emerald-500">Disetujui oleh</p>
                                <p class="text-sm font-bold text-emerald-800">{{ $transaction->approver->name }}</p>
                                @if($transaction->updated_at)
                                    <p class="text-[11px] text-emerald-400 mt-0.5">
                                        {{ $transaction->updated_at->format('d M Y, H:i') }} WITA
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Badge Nota Merah --}}
                @if($transaction->nota_merah_id)
                    <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <i class="ph ph-note-pencil text-red-500 text-lg flex-shrink-0"></i>
                            <div>
                                <p class="text-sm font-bold text-red-700 mb-0.5">Berasal dari Nota Merah</p>
                                <p class="text-xs text-red-500">Transaksi ini dibuat dari konfirmasi nota merah yang sudah selesai.</p>
                                <a href="{{ route('nota-merah.show', $transaction->nota_merah_id) }}"
                                    class="inline-flex items-center gap-1 text-xs font-semibold text-red-700 underline mt-1 hover:text-red-900 transition-colors">
                                    <i class="ph ph-arrow-square-out"></i>
                                    Lihat Nota Merah #{{ $transaction->nota_merah_id }}
                                </a>
                            </div>
                        </div>
                    </div>
            </div>
        </div>

        {{-- Banner: Menunggu Persetujuan --}}
        @if($transaction->status === 'pending')
            <div class="mt-5 p-5 bg-amber-50 border border-amber-200 rounded-2xl flex items-center gap-3">
                <i class="ph ph-clock text-amber-500 text-lg flex-shrink-0"></i>
                <div>
                    <p class="font-bold text-amber-800 text-sm">Menunggu persetujuan Admin.</p>
                    <p class="text-sm text-amber-700 mt-0.5">Transaksi Anda sedang dalam antrean review. Harap tunggu konfirmasi dari Admin.</p>
                </div>
            </div>
        @endif

        {{-- Banner: Edit & Kirim Ulang (saat ditolak) --}}
        @if($transaction->status === 'rejected')
            <div class="mt-5 p-5 bg-red-50 border border-red-200 rounded-2xl flex items-center justify-between flex-wrap gap-4">
                <div>
                    <p class="font-bold text-red-800 text-sm">Pengajuan ini ditolak oleh Admin.</p>
                    <p class="text-sm text-red-600 mt-0.5">Perbaiki data transaksi Anda, lalu kirim ulang untuk ditinjau.</p>
                </div>
                <a href="{{ route('transactions.edit', $transaction->id) }}"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-amber-500 text-white font-semibold text-sm hover:bg-amber-600 transition-colors whitespace-nowrap">
                    <i class="ph ph-pencil-simple"></i> Edit & Kirim Ulang
                </a>
            </div>
        @endif

        {{-- Banner: Transaksi Disetujui --}}
        @if($transaction->status === 'approved')
            <div class="mt-5 bg-emerald-50 border border-emerald-200 rounded-2xl p-4">
                <div class="flex items-center gap-3">
                    <i class="ph ph-check-circle text-emerald-500 text-lg flex-shrink-0"></i>
                    <p class="text-sm text-emerald-700">
                        <span class="font-bold">Transaksi ini sudah tercatat resmi di Buku Kas.</span>
                        Tidak dapat diedit atau dihapus.
                    </p>
                </div>
            </div>
        @endif

    </div>
@endsection
