@extends('layouts.admin')

@section('title', 'Nota Merah - SanthiGraha')
@section('page_title', 'Manajemen Nota Merah')

@section('content')

    {{-- Header --}}
    <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Nota Merah — Pengajuan Dana Pegawai</h2>
            <p class="text-sm text-slate-500 mt-1">Tinjau dan kelola pengajuan dana pegawai sebelum transaksi terjadi.</p>
        </div>
        {{-- Badge Antrean --}}
        <div class="flex gap-2 flex-wrap">
            @if($countMenungguPersetujuan > 0)
                <div
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 text-sm font-semibold">
                    <i class="ph ph-clock text-base"></i> {{ $countMenungguPersetujuan }} Menunggu Persetujuan
                </div>
            @endif
            @if($countMenungguKonfirmasi > 0)
                <div
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-purple-50 border border-purple-200 text-purple-700 text-sm font-semibold">
                    <i class="ph ph-hourglass text-base"></i> {{ $countMenungguKonfirmasi }} Menunggu Verifikasi Realisasi
                </div>
            @endif
            @if($countMenungguPersetujuan === 0 && $countMenungguKonfirmasi === 0)
                <div
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-semibold">
                    <i class="ph ph-check-circle text-base"></i> Semua Terproses
                </div>
            @endif
        </div>
    </div>

    {{-- Filter --}}
    <form method="GET" action="{{ route('nota-merah.index') }}" class="mb-5 flex flex-wrap gap-3">
        <div class="relative flex-1 min-w-[180px]">
            <i
                class="ph ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg pointer-events-none"></i>
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Cari proyek, kategori, deskripsi..."
                class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 outline-none bg-white">
        </div>
        <select name="status"
            class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm bg-white focus:ring-2 focus:ring-emerald-400 outline-none">
            <option value="">Semua Status</option>
            <option value="menunggu_persetujuan" @selected(request('status') === 'menunggu_persetujuan')>Menunggu Persetujuan
            </option>
            <option value="ditolak" @selected(request('status') === 'ditolak')>Ditolak</option>
            <option value="menunggu_konfirmasi" @selected(request('status') === 'menunggu_konfirmasi')>Admin Menunggu Realisasi
            </option>
            <option value="menunggu_verifikasi" @selected(request('status') === 'menunggu_verifikasi')>Menunggu Verifikasi
            </option>
            <option value="selesai" @selected(request('status') === 'selesai')>Selesai</option>
        </select>
        <select name="sort"
            class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm bg-white focus:ring-2 focus:ring-emerald-400 outline-none">
            <option value="latest" @selected(request('sort', 'latest') === 'latest')>Terbaru (Pengajuan)</option>
            <option value="oldest" @selected(request('sort') === 'oldest')>Terlama (Pengajuan)</option>
        </select>
        <button type="submit"
            class="px-5 py-2.5 rounded-xl bg-slate-700 text-white text-sm font-medium hover:bg-slate-800 transition-colors">Cari</button>
        @if(request()->anyFilled(['search', 'status', 'sort']))
            <a href="{{ route('nota-merah.index') }}"
                class="px-5 py-2.5 rounded-xl bg-slate-100 text-slate-600 text-sm font-medium hover:bg-slate-200 transition-colors">Reset</a>
        @endif
    </form>

    {{-- Tabel --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="px-5 py-4">TANGGAL / PENGAJU</th>
                        <th class="px-5 py-4">TANGGAL NOTA</th>
                        <th class="px-5 py-4">PROYEK & KATEGORI</th>
                        <th class="px-5 py-4">NOMINAL</th>
                        <th class="px-5 py-4">BUKTI</th>
                        <th class="px-5 py-4 text-center">STATUS</th>
                        <th class="px-5 py-4 text-center">AKSI ADMIN</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($notaMerahs as $nota)
                        <tr
                            class="hover:bg-slate-50 transition-colors {{ in_array($nota->status, ['menunggu_persetujuan', 'menunggu_konfirmasi']) ? 'border-l-4 border-l-amber-400' : '' }}">

                            {{-- Tanggal & Pengaju --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="font-medium text-slate-800">{{ $nota->created_at->format('d M Y') }}</div>
                                <div class="text-xs text-slate-400 mt-0.5">{{ $nota->created_at->format('H:i') }} WITA</div>
                                <div class="flex items-center gap-1.5 mt-2">
                                    <div
                                        class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-[10px] shrink-0">
                                        {{ substr($nota->user->name ?? 'U', 0, 1) }}
                                    </div>
                                    <span class="text-xs font-medium text-slate-600">{{ $nota->user->name ?? '-' }}</span>
                                </div>
                            </td>

                            {{-- Tanggal Nota --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="font-medium text-slate-700">{{ $nota->nota_date ? $nota->nota_date->format('d M Y') : '-' }}</div>
                            </td>

                            {{-- Proyek & Kategori --}}
                            <td class="px-5 py-4">
                                <div class="font-medium text-slate-800">{{ $nota->project->project_name ?? '-' }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">{{ $nota->category->category_name ?? '-' }}</div>
                                <div class="text-[12px] text-slate-400 mt-1 italic">
                                    {{ $nota->description ? Str::limit($nota->description, 55) : 'Nota Merah #' . $nota->id }}
                                </div>
                            </td>

                            {{-- Nominal --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="font-bold text-red-600">Rp {{ number_format($nota->amount, 2, ',', '.') }}</span>
                                @if($nota->bank_tujuan)
                                    <div class="text-[11px] text-slate-400 mt-0.5 font-medium flex items-center gap-1">
                                        <i class="ph ph-bank"></i> {{ $nota->bank_tujuan }}
                                    </div>
                                @endif
                            </td>

                            {{-- Bukti Foto --}}
                            <td class="px-5 py-4">
                                <div class="space-y-2">
                                    {{-- Nota Photo --}}
                                    @if($nota->nota_photo)
                                        <div>
                                            <div class="text-[10px] text-slate-400 font-semibold uppercase mb-1">Nota</div>
                                            @if(str_ends_with(strtolower($nota->nota_photo), '.pdf'))
                                                <a href="{{ asset('storage/' . $nota->nota_photo) }}" target="_blank"
                                                    class="inline-flex items-center gap-1 text-[11px] font-semibold text-red-600 bg-red-50 px-2.5 py-1 rounded-lg hover:bg-red-100 border border-red-100">
                                                    <i class="ph ph-file-pdf"></i> PDF
                                                </a>
                                            @else
                                                <a href="{{ asset('storage/' . $nota->nota_photo) }}" target="_blank">
                                                    <img src="{{ asset('storage/' . $nota->nota_photo) }}" alt="Nota"
                                                        class="h-12 w-16 object-cover rounded-lg border border-slate-200 hover:opacity-80 transition-opacity">
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                    {{-- Bukti Transfer (dari Admin) --}}
                                    @if($nota->transfer_proof)
                                        <div>
                                            <div class="text-[10px] text-emerald-500 font-semibold uppercase mb-1">Transfer</div>
                                            @if(str_ends_with(strtolower($nota->transfer_proof), '.pdf'))
                                                <a href="{{ asset('storage/' . $nota->transfer_proof) }}" target="_blank"
                                                    class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-lg hover:bg-emerald-100 border border-emerald-100">
                                                    <i class="ph ph-file-pdf"></i> PDF
                                                </a>
                                            @else
                                                <a href="{{ asset('storage/' . $nota->transfer_proof) }}" target="_blank">
                                                    <img src="{{ asset('storage/' . $nota->transfer_proof) }}" alt="Bukti Transfer"
                                                        class="h-12 w-16 object-cover rounded-lg border border-emerald-200 hover:opacity-80 transition-opacity">
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                    {{-- Realisasi Photo --}}
                                    @if($nota->realisasi_photo)
                                        <div>
                                            <div class="text-[10px] text-purple-500 font-semibold uppercase mb-1">Realisasi</div>
                                            @if(str_ends_with(strtolower($nota->realisasi_photo), '.pdf'))
                                                <a href="{{ asset('storage/' . $nota->realisasi_photo) }}" target="_blank"
                                                    class="inline-flex items-center gap-1 text-[11px] font-semibold text-purple-600 bg-purple-50 px-2.5 py-1 rounded-lg hover:bg-purple-100 border border-purple-100">
                                                    <i class="ph ph-file-pdf"></i> PDF
                                                </a>
                                            @else
                                                <a href="{{ asset('storage/' . $nota->realisasi_photo) }}" target="_blank">
                                                    <img src="{{ asset('storage/' . $nota->realisasi_photo) }}" alt="Realisasi"
                                                        class="h-12 w-16 object-cover rounded-lg border border-purple-200 hover:opacity-80 transition-opacity">
                                                </a>
                                            @endif
                                        </div>
                                    @endif
                                    @if(!$nota->nota_photo && !$nota->transfer_proof && !$nota->realisasi_photo)
                                        <span class="text-xs text-slate-400 italic">—</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Status --}}
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $nota->status_color }}">
                                    @if($nota->status === 'menunggu_persetujuan') <i class="ph ph-clock"></i>
                                    @elseif($nota->status === 'disetujui') <i class="ph ph-check-circle"></i>
                                    @elseif($nota->status === 'ditolak') <i class="ph ph-x-circle"></i>
                                    @elseif($nota->status === 'menunggu_konfirmasi') <i class="ph ph-hourglass"></i>
                                    @elseif($nota->status === 'selesai') <i class="ph ph-check-square"></i>
                                    @endif
                                    {{ $nota->status_label }}
                                </span>

                                @if(($nota->status === 'ditolak' || $nota->status === 'menunggu_konfirmasi') && $nota->rejection_reason)
                                    <div
                                        class="text-[10px] text-red-400 italic mt-1.5 max-w-[130px] mx-auto leading-relaxed text-left">
                                        <strong>{{ $nota->status === 'menunggu_konfirmasi' ? 'Realisasi Ditolak: ' : '' }}</strong>{{ Str::limit($nota->rejection_reason, 50) }}
                                    </div>
                                @endif
                                @if($nota->status === 'menunggu_konfirmasi' && $nota->realisasi_date)
                                    <div class="text-[10px] text-purple-500 mt-1.5">
                                        <i class="ph ph-calendar"></i>
                                        {{ \Carbon\Carbon::parse($nota->realisasi_date)->format('d M Y') }}
                                    </div>
                                @endif
                                @if($nota->status === 'selesai' && $nota->confirmed_at)
                                    <div class="text-[10px] text-emerald-500 mt-1.5">
                                        ✓ {{ $nota->confirmed_at->format('d M Y') }}
                                    </div>
                                @endif
                            </td>

                            {{-- Aksi Admin --}}
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                <div class="flex flex-col items-center gap-2">

                                    {{-- Lihat Detail --}}
                                    <a href="{{ route('nota-merah.show', $nota->id) }}"
                                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-100 text-slate-600 text-xs font-semibold hover:bg-slate-200 transition-colors">
                                        <i class="ph ph-eye text-sm"></i> Detail
                                    </a>

                                    {{-- Tahap 1: Menunggu Persetujuan → ke form upload bukti transfer --}}
                                    @if($nota->status === 'menunggu_persetujuan')
                                        <a href="{{ route('nota-merah.approve.form', $nota->id) }}"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-emerald-500 text-white font-semibold text-xs hover:bg-emerald-600 hover:shadow-lg hover:shadow-emerald-500/20 transition-all">
                                            <i class="ph ph-check-circle text-sm"></i> Setujui
                                        </a>
                                        <button onclick="rejectNota({{ $nota->id }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-50 text-red-600 font-semibold text-xs hover:bg-red-500 hover:text-white transition-all border border-red-100 hover:border-red-500">
                                            <i class="ph ph-x-circle text-sm"></i> Tolak
                                        </button>

                                        {{-- Tahap 2: Menunggu Konfirmasi — admin sudah transfer, tunggu pegawai upload realisasi
                                        --}}
                                    @elseif($nota->status === 'menunggu_konfirmasi')
                                        <span class="text-xs text-blue-600 font-semibold flex items-center gap-1">
                                            <i class="ph ph-clock"></i> Menunggu Realisasi
                                        </span>
                                        <span class="text-[10px] text-slate-400 flex items-center gap-1">
                                            <i class="ph ph-check"></i> Transfer sudah diupload
                                        </span>

                                        {{-- Tahap 3: Menunggu Verifikasi — pegawai sudah upload realisasi, admin perlu konfirmasi
                                        --}}
                                    @elseif($nota->status === 'menunggu_verifikasi')
                                        <form action="{{ route('nota-merah.confirm', $nota->id) }}" method="POST" class="w-full">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-purple-500 text-white font-semibold text-xs hover:bg-purple-600 hover:shadow-lg hover:shadow-purple-500/20 transition-all w-full justify-center">
                                                <i class="ph ph-check-square text-sm"></i> Konfirmasi & Catat Kas
                                            </button>
                                        </form>
                                        <button onclick="rejectRealisasi({{ $nota->id }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-50 text-red-600 font-semibold text-xs hover:bg-red-500 hover:text-white transition-all border border-red-100 hover:border-red-500 w-full justify-center">
                                            <i class="ph ph-x-circle text-sm"></i> Tolak Realisasi
                                        </button>

                                        {{-- Selesai --}}
                                    @elseif($nota->status === 'selesai')
                                        <span class="text-xs text-emerald-600 font-semibold flex items-center gap-1">
                                            <i class="ph ph-check-square"></i> Tercatat di Kas
                                        </span>
                                        @if($nota->transaction)
                                            <span class="text-[10px] text-slate-400">Trx #{{ $nota->transaction->id }}</span>
                                        @endif
                                    @endif

                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-16 text-center">
                                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="ph ph-file-text text-3xl text-slate-300"></i>
                                </div>
                                <p class="text-base font-bold text-slate-800">Tidak Ada Data</p>
                                <p class="text-sm text-slate-500 mt-1">Belum ada nota merah yang diajukan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($notaMerahs->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 text-sm">
                {{ $notaMerahs->links() }}
            </div>
        @endif
    </div>

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

        function rejectNota(id) {
            Swal.fire({
                title: 'Tolak Nota Merah',
                text: 'Berikan alasan penolakan yang jelas agar pegawai dapat memahami.',
                input: 'textarea',
                inputPlaceholder: 'Ketik alasan penolakan di sini...',
                showCancelButton: true,
                confirmButtonText: 'Tolak Pengajuan',
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
                    form.action = `/nota-merah/${id}/reject`;
                    document.getElementById('rejectNotaReason').value = result.value;
                    form.submit();
                }
            });
        }
    </script>

@endsection