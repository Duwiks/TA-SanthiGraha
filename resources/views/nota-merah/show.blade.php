@extends('layouts.pegawai')

@section('title', 'Detail Nota Merah #{{ $nota->id }} - SanthiGraha')
@section('page_title', 'Detail Nota Merah')

@section('content')
<div class="max-w-3xl mx-auto">

    <div class="mb-6">
        <a href="{{ route('nota-merah.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-emerald-600 transition-colors mb-4">
            <i class="ph ph-arrow-left"></i> Kembali ke Daftar
        </a>
        <div class="flex items-center justify-between flex-wrap gap-3">
            <div>
                <h2 class="text-lg font-bold text-slate-800">Nota Merah #{{ $nota->id }}</h2>
                <p class="text-sm text-slate-500 mt-0.5">Diajukan pada {{ $nota->created_at->format('d M Y, H:i') }} WIB</p>
            </div>
            <span class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold {{ $nota->status_color }}">
                @if($nota->status === 'menunggu_persetujuan') <i class="ph ph-clock text-base"></i>
                @elseif($nota->status === 'disetujui') <i class="ph ph-check-circle text-base"></i>
                @elseif($nota->status === 'ditolak') <i class="ph ph-x-circle text-base"></i>
                @elseif($nota->status === 'menunggu_konfirmasi') <i class="ph ph-hourglass text-base"></i>
                @elseif($nota->status === 'selesai') <i class="ph ph-check-square text-base"></i>
                @endif
                {{ $nota->status_label }}
            </span>
        </div>
    </div>

    {{-- Timeline Status --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 mb-5">
        <h3 class="text-sm font-bold text-slate-700 mb-4">Alur Proses</h3>
        <div class="flex items-center gap-1 flex-wrap">
            @php
                $stages = [
                    ['key' => 'menunggu_persetujuan', 'label' => 'Diajukan', 'icon' => 'ph-paper-plane-tilt'],
                    ['key' => 'disetujui',            'label' => 'Disetujui', 'icon' => 'ph-check-circle'],
                    ['key' => 'menunggu_konfirmasi',  'label' => 'Realisasi', 'icon' => 'ph-upload-simple'],
                    ['key' => 'selesai',              'label' => 'Kas Dicatat', 'icon' => 'ph-check-square'],
                ];
                $order = ['menunggu_persetujuan' => 0, 'disetujui' => 1, 'ditolak' => 1, 'menunggu_konfirmasi' => 2, 'selesai' => 3];
                $currentOrder = $order[$nota->status] ?? 0;
            @endphp
            @foreach($stages as $i => $stage)
                @php
                    $stageOrder = $i;
                    $isDone = $currentOrder > $stageOrder;
                    $isActive = ($nota->status !== 'ditolak' && $currentOrder === $stageOrder) ||
                                ($nota->status === 'ditolak' && $stageOrder === 0);
                @endphp
                <div class="flex items-center gap-1">
                    <div class="flex flex-col items-center">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold
                            {{ $isDone ? 'bg-emerald-500 text-white' : ($isActive ? 'bg-blue-500 text-white ring-4 ring-blue-100' : 'bg-slate-100 text-slate-400') }}">
                            @if($isDone)
                                <i class="ph ph-check-bold"></i>
                            @else
                                <i class="ph {{ $stage['icon'] }}"></i>
                            @endif
                        </div>
                        <span class="text-[10px] font-medium mt-1
                            {{ $isDone ? 'text-emerald-600' : ($isActive ? 'text-blue-600' : 'text-slate-400') }}">
                            {{ $stage['label'] }}
                        </span>
                    </div>
                    @if($i < count($stages) - 1)
                        <div class="w-10 h-0.5 mb-4 {{ $isDone ? 'bg-emerald-300' : 'bg-slate-200' }}"></div>
                    @endif
                </div>
            @endforeach

            @if($nota->status === 'ditolak')
                <div class="ml-2 flex items-center gap-2 px-3 py-1.5 bg-red-50 rounded-lg border border-red-100">
                    <i class="ph ph-x-circle text-red-500 text-base"></i>
                    <span class="text-xs font-semibold text-red-600">Ditolak</span>
                </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">

        {{-- Informasi Pengajuan --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6">
            <h3 class="text-sm font-bold text-slate-700 mb-4 flex items-center gap-2">
                <i class="ph ph-file-text text-emerald-500"></i> Detail Pengajuan
            </h3>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-slate-500">Proyek</dt>
                    <dd class="font-semibold text-slate-800">{{ $nota->project->project_name ?? '-' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Kategori</dt>
                    <dd class="font-semibold text-slate-800">{{ $nota->category->category_name ?? '-' }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Nominal</dt>
                    <dd class="font-bold text-red-600 text-base">Rp {{ number_format($nota->amount, 0, ',', '.') }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-slate-500">Metode Pencairan</dt>
                    <dd class="font-semibold text-slate-800">{{ $nota->payment_method }}</dd>
                </div>
                @if($nota->description)
                <div class="pt-2 border-t border-slate-100">
                    <dt class="text-slate-500 mb-1">Keterangan</dt>
                    <dd class="text-slate-700 leading-relaxed">{{ $nota->description }}</dd>
                </div>
                @endif
            </dl>
        </div>

        {{-- Foto & Status Info --}}
        <div class="space-y-4">

            {{-- Nota Merah Photo --}}
            @if($nota->nota_photo)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <h3 class="text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                    <i class="ph ph-image text-amber-500"></i> Foto Nota Merah
                </h3>
                @if(str_ends_with(strtolower($nota->nota_photo), '.pdf'))
                    <a href="{{ asset('storage/' . $nota->nota_photo) }}" target="_blank"
                       class="flex items-center gap-2 px-4 py-3 rounded-xl bg-red-50 text-red-600 font-medium text-sm hover:bg-red-100 transition-colors border border-red-100">
                        <i class="ph ph-file-pdf text-xl"></i> Lihat Dokumen PDF
                    </a>
                @else
                    <a href="{{ asset('storage/' . $nota->nota_photo) }}" target="_blank">
                        <img src="{{ asset('storage/' . $nota->nota_photo) }}" alt="Nota Merah"
                             class="w-full h-40 object-cover rounded-xl border border-slate-200 hover:opacity-80 transition-opacity">
                    </a>
                @endif
            </div>
            @endif

            {{-- Realisasi Photo --}}
            @if($nota->realisasi_photo)
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                <h3 class="text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                    <i class="ph ph-receipt text-purple-500"></i> Bukti Realisasi
                    @if($nota->realisasi_date)
                        <span class="text-[11px] font-normal text-slate-400 ml-auto">{{ \Carbon\Carbon::parse($nota->realisasi_date)->format('d M Y') }}</span>
                    @endif
                </h3>
                @if(str_ends_with(strtolower($nota->realisasi_photo), '.pdf'))
                    <a href="{{ asset('storage/' . $nota->realisasi_photo) }}" target="_blank"
                       class="flex items-center gap-2 px-4 py-3 rounded-xl bg-red-50 text-red-600 font-medium text-sm hover:bg-red-100 transition-colors border border-red-100">
                        <i class="ph ph-file-pdf text-xl"></i> Lihat Dokumen PDF
                    </a>
                @else
                    <a href="{{ asset('storage/' . $nota->realisasi_photo) }}" target="_blank">
                        <img src="{{ asset('storage/' . $nota->realisasi_photo) }}" alt="Bukti Realisasi"
                             class="w-full h-40 object-cover rounded-xl border border-slate-200 hover:opacity-80 transition-opacity">
                    </a>
                @endif
            </div>
            @endif

            {{-- Info Penolakan --}}
            @if($nota->status === 'ditolak' && $nota->rejection_reason)
            <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <i class="ph ph-warning text-red-500 text-lg flex-shrink-0 mt-0.5"></i>
                    <div>
                        <p class="text-sm font-bold text-red-700 mb-1">Alasan Penolakan</p>
                        <p class="text-sm text-red-600 leading-relaxed">{{ $nota->rejection_reason }}</p>
                        @if($nota->approver)
                            <p class="text-xs text-red-400 mt-2">— oleh {{ $nota->approver->name }}</p>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- Info Selesai --}}
            @if($nota->status === 'selesai')
            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-4">
                <div class="flex items-start gap-3">
                    <i class="ph ph-check-circle text-emerald-500 text-lg flex-shrink-0 mt-0.5"></i>
                    <div>
                        <p class="text-sm font-bold text-emerald-700 mb-1">Transaksi Tercatat di Kas</p>
                        <p class="text-sm text-emerald-600">Dikonfirmasi oleh {{ $nota->approver->name ?? 'Admin' }}
                            {{ $nota->confirmed_at ? 'pada ' . $nota->confirmed_at->format('d M Y, H:i') . ' WIB' : '' }}
                        </p>
                        @if($nota->transaction)
                            <a href="{{ route('transactions.index') }}" class="text-xs font-semibold text-emerald-700 underline mt-1 inline-block">
                                Lihat di Buku Kas →
                            </a>
                        @endif
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    {{-- Tombol Upload Realisasi --}}
    @if($nota->status === 'disetujui')
    <div class="mt-5 p-5 bg-blue-50 border border-blue-200 rounded-2xl flex items-center justify-between flex-wrap gap-4">
        <div>
            <p class="font-bold text-blue-800 text-sm">Nota merah Anda telah disetujui!</p>
            <p class="text-sm text-blue-600 mt-0.5">Lakukan pembelian sesuai pengajuan, kemudian upload bukti struk atau kwitansi.</p>
        </div>
        <a href="{{ route('nota-merah.realisasi.form', $nota->id) }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-purple-500 text-white font-semibold text-sm hover:bg-purple-600 transition-colors whitespace-nowrap">
            <i class="ph ph-upload-simple"></i> Upload Bukti Realisasi
        </a>
    </div>
    @endif

    {{-- Tombol Edit & Kirim Ulang (saat ditolak) --}}
    @if($nota->status === 'ditolak' && auth()->user()->role === 'pegawai' && $nota->user_id === auth()->id())
    <div class="mt-5 p-5 bg-red-50 border border-red-200 rounded-2xl flex items-center justify-between flex-wrap gap-4">
        <div>
            <p class="font-bold text-red-800 text-sm">Pengajuan ini ditolak oleh Admin.</p>
            <p class="text-sm text-red-600 mt-0.5">Perbaiki data sesuai alasan penolakan di atas, lalu kirim ulang untuk ditinjau.</p>
        </div>
        <a href="{{ route('nota-merah.edit', $nota->id) }}"
           class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-amber-500 text-white font-semibold text-sm hover:bg-amber-600 transition-colors whitespace-nowrap">
            <i class="ph ph-pencil-simple"></i> Edit & Kirim Ulang
        </a>
    </div>
    @endif

</div>
@endsection
