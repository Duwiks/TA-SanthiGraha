@extends(auth()->user()->role === 'admin' ? 'layouts.admin' : 'layouts.pegawai')

@section('title', 'Detail Nota Merah #{{ $nota->id }} - SanthiGraha')
@section('page_title', 'Detail Nota Merah')

@section('content')
    <div class="max-w-3xl mx-auto">

        <div class="mb-6">
            <a href="{{ route('nota-merah.index') }}"
                class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-emerald-600 transition-colors mb-4">
                <i class="ph ph-arrow-left"></i> Kembali ke Daftar
            </a>
            <div class="flex items-center justify-between flex-wrap gap-3">
                <div>
                    <h2 class="text-lg font-bold text-slate-800">Nota Merah #{{ $nota->id }}</h2>
                    <p class="text-sm text-slate-500 mt-0.5">Diajukan pada {{ $nota->created_at->format('d M Y, H:i') }} WITA
                    </p>
                </div>
                <span
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold {{ $nota->status_color }}">
                    @if($nota->status === 'menunggu_persetujuan') <i class="ph ph-clock text-base"></i>
                    @elseif($nota->status === 'ditolak') <i class="ph ph-x-circle text-base"></i>
                    @elseif($nota->status === 'menunggu_konfirmasi') <i class="ph ph-bank text-base"></i>
                    @elseif($nota->status === 'menunggu_verifikasi') <i class="ph ph-hourglass text-base"></i>
                    @elseif($nota->status === 'selesai') <i class="ph ph-check-square text-base"></i>
                    @endif
                    {{ auth()->user()->role === 'pegawai' && $nota->status === 'menunggu_konfirmasi' ? 'Admin Menunggu Realisasi' : $nota->status_label }}
                </span>
            </div>
        </div>

        {{-- Timeline Status --}}
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 mb-5">
            <h3 class="text-sm font-bold text-slate-700 mb-4">Alur Proses</h3>
            <div class="flex items-center gap-1 flex-wrap">
                @php
                    $stages = [
                        ['key' => 'menunggu_persetujuan', 'label' => 'Diajukan',     'icon' => 'ph-paper-plane-tilt'],
                        ['key' => 'menunggu_konfirmasi',  'label' => 'Ditransfer',   'icon' => 'ph-bank'],
                        ['key' => 'menunggu_verifikasi',  'label' => 'Realisasi',    'icon' => 'ph-upload-simple'],
                        ['key' => 'selesai',              'label' => 'Kas Dicatat',  'icon' => 'ph-check-square'],
                    ];
                    $order = [
                        'menunggu_persetujuan' => 0,
                        'ditolak'              => 0,
                        'menunggu_konfirmasi'  => 1,
                        'menunggu_verifikasi'  => 2,
                        'selesai'              => 3,
                    ];
                    $currentOrder = $order[$nota->status] ?? 0;
                @endphp
                @foreach($stages as $i => $stage)
                    @php
                        $stageOrder = $i;
                        $isDone     = $currentOrder > $stageOrder;
                        $isActive   = ($nota->status !== 'ditolak' && $currentOrder === $stageOrder) ||
                                      ($nota->status === 'ditolak' && $stageOrder === 0);
                    @endphp
                    <div class="flex items-center gap-1">
                        <div class="flex flex-col items-center">
                            <div
                                class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold
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
                        <dt class="text-slate-500">Tanggal Nota</dt>
                        <dd class="font-semibold text-slate-800">{{ $nota->nota_date ? $nota->nota_date->format('d M Y') : '-' }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-slate-500">Nominal</dt>
                        <dd class="font-bold text-red-600 text-base">Rp {{ number_format($nota->amount, 2, ',', '.') }}</dd>
                    </div>

                    {{-- Rekening Tujuan --}}
                    @if($nota->bank_tujuan || $nota->no_rekening || $nota->nama_pemilik_rekening)
                        <div class="pt-2 border-t border-slate-100">
                            <dt class="text-slate-500 mb-2 font-medium flex items-center gap-1">
                                <i class="ph ph-bank text-emerald-400 text-sm"></i> Rekening Tujuan
                            </dt>
                            @if($nota->bank_tujuan)
                            <dd class="text-slate-700 text-xs mb-1">
                                <span class="text-slate-400">Bank:</span>
                                <span class="font-semibold ml-1">{{ $nota->bank_tujuan }}</span>
                            </dd>
                            @endif
                            @if($nota->no_rekening)
                            <dd class="text-slate-700 text-xs mb-1">
                                <span class="text-slate-400">No. Rek:</span>
                                <span class="font-semibold ml-1 font-mono tracking-wider">{{ $nota->no_rekening }}</span>
                            </dd>
                            @endif
                            @if($nota->nama_pemilik_rekening)
                            <dd class="text-slate-700 text-xs">
                                <span class="text-slate-400">A.n.:</span>
                                <span class="font-semibold ml-1">{{ $nota->nama_pemilik_rekening }}</span>
                            </dd>
                            @endif
                        </div>
                    @endif

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

                {{-- Foto Bukti Transfer (dari Admin) --}}
                @if($nota->transfer_proof)
                    <div class="bg-white rounded-2xl border border-emerald-100 shadow-sm p-5">
                        <h3 class="text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                            <i class="ph ph-money-wavy text-emerald-500"></i> Foto Bukti Transfer
                            <span class="ml-auto text-[10px] font-normal text-emerald-500 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-100">
                                Dari Admin
                            </span>
                        </h3>
                        @if(str_ends_with(strtolower($nota->transfer_proof), '.pdf'))
                            <a href="{{ asset('storage/' . $nota->transfer_proof) }}" target="_blank"
                                class="flex items-center gap-2 px-4 py-3 rounded-xl bg-red-50 text-red-600 font-medium text-sm hover:bg-red-100 transition-colors border border-red-100">
                                <i class="ph ph-file-pdf text-xl"></i> Lihat Dokumen PDF
                            </a>
                        @else
                            <a href="{{ asset('storage/' . $nota->transfer_proof) }}" target="_blank">
                                <img src="{{ asset('storage/' . $nota->transfer_proof) }}" alt="Bukti Transfer"
                                    class="w-full h-40 object-cover rounded-xl border border-emerald-200 hover:opacity-80 transition-opacity">
                            </a>
                        @endif
                        @if($nota->approver)
                            <p class="text-[11px] text-slate-400 mt-2 flex items-center gap-1">
                                <i class="ph ph-user-circle"></i> Diupload oleh {{ $nota->approver->name }}
                            </p>
                        @endif
                    </div>
                @endif

                {{-- Realisasi Photo --}}
                @if($nota->realisasi_photo)
                    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                        <h3 class="text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                            <i class="ph ph-receipt text-purple-500"></i> Bukti Realisasi
                            @if($nota->realisasi_date)
                                <span
                                    class="text-[11px] font-normal text-slate-400 ml-auto">{{ \Carbon\Carbon::parse($nota->realisasi_date)->format('d M Y') }}</span>
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
                @if(($nota->status === 'ditolak' || $nota->status === 'menunggu_konfirmasi') && $nota->rejection_reason)
                    <div class="bg-red-50 border border-red-200 rounded-xl p-4">
                        <div class="flex items-start gap-3">
                            <i class="ph ph-warning text-red-500 text-lg flex-shrink-0 mt-0.5"></i>
                            <div>
                                <p class="text-sm font-bold text-red-700 mb-1">
                                    {{ $nota->status === 'menunggu_konfirmasi' ? 'Bukti Realisasi Ditolak' : 'Alasan Penolakan' }}
                                </p>
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
                                    {{ $nota->confirmed_at ? 'pada ' . $nota->confirmed_at->format('d M Y, H:i') . ' WITA' : '' }}
                                </p>
                                @if($nota->transaction)
                                    <a href="{{ route('transactions.index') }}"
                                        class="text-xs font-semibold text-emerald-700 underline mt-1 inline-block">
                                        Lihat di Buku Kas →
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        {{-- Banner: Menunggu Konfirmasi — Pegawai Upload Realisasi --}}
        @if($nota->status === 'menunggu_konfirmasi' && auth()->user()->role === 'pegawai' && $nota->user_id === auth()->id())
            <div class="mt-5 p-5 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-start justify-between flex-wrap gap-4">
                <div>
                    <p class="font-bold text-emerald-800 text-sm">Dana telah ditransfer oleh Admin!</p>
                    <p class="text-sm text-emerald-700 mt-0.5">Lakukan pembelian sesuai pengajuan, kemudian upload bukti struk atau kwitansi sebagai bukti realisasi.</p>
                </div>
                <a href="{{ route('nota-merah.realisasi.form', $nota->id) }}"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-500 text-white font-semibold text-sm hover:bg-emerald-600 transition-colors whitespace-nowrap">
                    <i class="ph ph-upload-simple"></i> Upload Bukti Realisasi
                </a>
            </div>
        @endif

        {{-- Banner: Menunggu Verifikasi — Pegawai sudah upload, menunggu admin konfirmasi --}}
        @if($nota->status === 'menunggu_verifikasi' && auth()->user()->role === 'pegawai' && $nota->user_id === auth()->id())
            <div class="mt-5 p-5 bg-purple-50 border border-purple-200 rounded-2xl flex items-center gap-3">
                <i class="ph ph-hourglass text-purple-500 text-lg flex-shrink-0"></i>
                <div>
                    <p class="font-bold text-purple-800 text-sm">Bukti realisasi sudah diupload!</p>
                    <p class="text-sm text-purple-700 mt-0.5">Admin sedang memverifikasi foto realisasi Anda sebelum transaksi dicatat di buku kas.</p>
                </div>
            </div>
        @endif

        {{-- Banner: Admin melihat nota menunggu konfirmasi (tunggu realisasi pegawai) --}}
        @if($nota->status === 'menunggu_konfirmasi' && auth()->user()->role === 'admin')
            <div class="mt-5 p-5 bg-blue-50 border border-blue-200 rounded-2xl flex items-center gap-3">
                <i class="ph ph-clock text-blue-400 text-lg"></i>
                <p class="text-sm text-blue-700">Bukti transfer sudah diupload. Menunggu pegawai mengupload bukti realisasi pembelian.</p>
            </div>
        @endif

        {{-- Banner: Admin melihat nota menunggu verifikasi — ada tombol konfirmasi --}}
        @if($nota->status === 'menunggu_verifikasi' && auth()->user()->role === 'admin')
            <div class="mt-5 p-5 bg-purple-50 border border-purple-200 rounded-2xl flex items-start justify-between flex-wrap gap-4">
                <div>
                    <p class="font-bold text-purple-800 text-sm">Pegawai telah mengupload bukti realisasi!</p>
                    <p class="text-sm text-purple-700 mt-0.5">Periksa foto bukti realisasi di atas. Jika valid, konfirmasi untuk mencatat transaksi di buku kas.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button onclick="rejectRealisasi({{ $nota->id }})"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-red-50 text-red-600 font-semibold text-sm hover:bg-red-500 hover:text-white transition-colors whitespace-nowrap border border-red-100 hover:border-red-500">
                        <i class="ph ph-x-circle"></i> Tolak Realisasi
                    </button>
                    <form action="{{ route('nota-merah.confirm', $nota->id) }}" method="POST">
                        @csrf
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-purple-500 text-white font-semibold text-sm hover:bg-purple-600 transition-colors whitespace-nowrap">
                            <i class="ph ph-check-square"></i> Konfirmasi & Catat Kas
                        </button>
                    </form>
                </div>
            </div>
        @endif

        {{-- Banner Edit & Kirim Ulang (saat ditolak) --}}
        @if($nota->status === 'ditolak' && auth()->user()->role === 'pegawai' && $nota->user_id === auth()->id())
            <div class="mt-5 p-5 bg-red-50 border border-red-200 rounded-2xl flex items-center justify-between flex-wrap gap-4">
                <div>
                    <p class="font-bold text-red-800 text-sm">Pengajuan ini ditolak oleh Admin.</p>
                    <p class="text-sm text-red-600 mt-0.5">Perbaiki data sesuai alasan penolakan di atas, lalu kirim ulang untuk
                        ditinjau.</p>
                </div>
                <a href="{{ route('nota-merah.edit', $nota->id) }}"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-amber-500 text-white font-semibold text-sm hover:bg-amber-600 transition-colors whitespace-nowrap">
                    <i class="ph ph-pencil-simple"></i> Edit & Kirim Ulang
                </a>
            </div>
        @endif

    </div>

    @if(auth()->user()->role === 'admin')
        {{-- Hidden reject form --}}
        <form id="rejectNotaForm" method="POST" action="" class="hidden">
            @csrf
            <input type="hidden" name="reason" id="rejectNotaReason">
        </form>

        <script>
            function rejectRealisasi(id) {
                Swal.fire({
                    title: 'Tolak Bukti Realisasi',
                    text: 'Berikan alasan penolakan bukti realisasi agar pegawai dapat memperbaikinya.',
                    input: 'textarea',
                    inputPlaceholder: 'Ketik alasan penolakan di sini...',
                    showCancelButton: true,
                    confirmButtonText: 'Tolak Realisasi',
                    confirmButtonColor: '#ef4444',
                    cancelButtonText: 'Batal',
                    inputAttributes: { style: 'min-height: 80px' },
                    inputValidator: (value) => {
                        if (!value || value.trim().length < 5) {
                            return 'Alasan penolakan minimal 5 karakter!'
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        const form = document.getElementById('rejectNotaForm');
                        form.action = `/nota-merah/${id}/reject-realisasi`;
                        document.getElementById('rejectNotaReason').value = result.value;
                        form.submit();
                    }
                });
            }
        </script>
    @endif
@endsection