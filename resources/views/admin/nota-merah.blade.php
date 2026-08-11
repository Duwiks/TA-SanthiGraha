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
            <option value="menunggu_konfirmasi" @selected(request('status') === 'menunggu_konfirmasi')>Admin Menunggu
                Realisasi
            </option>
            <option value="menunggu_verifikasi" @selected(request('status') === 'menunggu_verifikasi')>Menunggu Verifikasi
            </option>
            <option value="selesai" @selected(request('status') === 'selesai')>Selesai</option>
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
            <option value="latest" @selected(request('sort', 'latest') === 'latest')>Terbaru (Pengajuan)</option>
            <option value="oldest" @selected(request('sort') === 'oldest')>Terlama (Pengajuan)</option>
        </select>
        <button type="submit"
            class="px-5 py-2.5 rounded-xl bg-slate-700 text-white text-sm font-medium hover:bg-slate-800 transition-colors">Cari</button>
        @if(request()->anyFilled(['search', 'status', 'year']))
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
                                <div class="font-medium text-slate-700">
                                    {{ $nota->nota_date ? $nota->nota_date->format('d M Y') : '-' }}
                                </div>
                            </td>

                            {{-- Proyek & Kategori --}}
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-1.5 flex-wrap mb-1">
                                    <span class="font-medium text-slate-800">{{ $nota->project->project_name ?? '-' }}</span>
                                    @php
                                        $stageColor = match($nota->payment_stage) {
                                            'uang_muka' => 'bg-blue-100 text-blue-700 border-blue-200',
                                            'proses'    => 'bg-amber-100 text-amber-700 border-amber-200',
                                            'selesai'   => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                                            default     => 'bg-slate-100 text-slate-700 border-slate-200',
                                        };
                                        $stageLabel = match($nota->payment_stage) {
                                            'uang_muka' => 'Uang Muka',
                                            'proses'    => 'Proses',
                                            'selesai'   => 'Selesai',
                                            default     => ucfirst($nota->payment_stage ?? '-'),
                                        };
                                    @endphp
                                    <span class="px-2 py-0.5 rounded text-[10px] font-semibold border {{ $stageColor }}">
                                        {{ $stageLabel }}
                                    </span>
                                </div>
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
                                        <button type="button" onclick="handleConfirmNotaClick({{ $nota->id }}, {{ $nota->project_id }}, {{ $nota->category_id }}, '{{ $nota->payment_stage ?? 'proses' }}')"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-purple-500 text-white font-semibold text-xs hover:bg-purple-600 hover:shadow-lg hover:shadow-purple-500/20 transition-all w-full justify-center">
                                            <i class="ph ph-check-square text-sm"></i> Konfirmasi & Catat Kas
                                        </button>
                                        <button onclick="rejectRealisasi({{ $nota->id }})"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-red-50 text-red-600 font-semibold text-xs hover:bg-red-500 hover:text-white transition-all border border-red-100 hover:border-red-500 w-full justify-center">
                                            <i class="ph ph-x-circle text-sm"></i> Tolak Realisasi
                                        </button>

                                        {{-- Selesai --}}
                                    @elseif($nota->status === 'selesai')
                                        <span class="text-xs text-emerald-600 font-semibold flex items-center gap-1">
                                            <i class="ph ph-check-square"></i> Tercatat di Kas
                                        </span>
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

    {{-- Hidden Direct Confirm Form --}}
    <form id="directConfirmNotaForm" method="POST" action="" class="hidden">
        @csrf
        <input type="hidden" name="payment_stage" id="directNotaPaymentStage">
        <input type="hidden" name="payment_group_action" id="directNotaGroupAction">
        <input type="hidden" name="payment_group_label" id="directNotaGroupLabel">
    </form>

    {{-- Modal Konfirmasi Approval Payment Group Selesai (Nota Merah) --}}
    <div id="confirmNotaGroupModal"
        class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm hidden p-4">
        <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6 relative animate-in fade-in zoom-in duration-200">
            {{-- Icon & Judul --}}
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                    <i class="ph ph-warning-circle text-2xl"></i>
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-base">Kelompok Sebelumnya Selesai</h3>
                    <p class="text-xs text-slate-500">Proyek & kategori nota merah ini sebelumnya sudah selesai.</p>
                </div>
            </div>

            {{-- Info Group Sebelumnya --}}
            <div class="bg-slate-50 rounded-xl p-4 mb-4 text-xs space-y-1.5 border border-slate-100">
                <div class="flex justify-between">
                    <span class="text-slate-500">Proyek:</span>
                    <span class="font-semibold text-slate-700" id="modal_nota_project_name">-</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-slate-500">Kategori:</span>
                    <span class="font-semibold text-slate-700" id="modal_nota_category_name">-</span>
                </div>
                <div class="flex justify-between" id="modal_nota_label_row">
                    <span class="text-slate-500">Label Sebelumnya:</span>
                    <span class="font-semibold text-slate-700" id="modal_nota_label">-</span>
                </div>
                <div class="flex justify-between border-t border-slate-200 pt-1.5 mt-1.5">
                    <span class="text-slate-500">Total Sebelumnya:</span>
                    <span class="font-bold text-emerald-600" id="modal_nota_total_amount">-</span>
                </div>
            </div>

            {{-- Pertanyaan / Pilihan --}}
            <div class="mb-4">
                <label class="block text-xs font-semibold text-slate-700 mb-1.5">Tindakan Kelompok Pembayaran:</label>
                <div class="grid grid-cols-2 gap-2">
                    <label class="flex items-center gap-2 p-3 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition-colors has-[:checked]:border-brand-500 has-[:checked]:bg-indigo-50/40">
                        <input type="radio" name="modal_nota_group_action" value="lanjutkan" checked onchange="toggleNotaNewGroupLabel(this.value)" class="text-brand-500">
                        <span class="text-xs font-medium text-slate-700">Lanjutkan Kelompok Lama</span>
                    </label>
                    <label class="flex items-center gap-2 p-3 border border-slate-200 rounded-xl cursor-pointer hover:bg-slate-50 transition-colors has-[:checked]:border-brand-500 has-[:checked]:bg-indigo-50/40">
                        <input type="radio" name="modal_nota_group_action" value="baru" onchange="toggleNotaNewGroupLabel(this.value)" class="text-brand-500">
                        <span class="text-xs font-medium text-slate-700">Buat Kelompok Baru</span>
                    </label>
                </div>
            </div>

            {{-- Input Label Baru (hidden default) --}}
            <div id="notaNewGroupLabelSection" class="hidden mb-4">
                <label class="block text-xs font-semibold text-slate-700 mb-1">
                    Label Kelompok Baru <span class="text-red-500">*</span>
                </label>
                <input type="text" id="modal_nota_new_label" placeholder="Contoh: Tahap 2, Perbaikan Lanjutan"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-400 outline-none">
                <p class="text-[11px] text-red-500 mt-1 hidden" id="modal_nota_label_error">Label kelompok baru wajib diisi.</p>
            </div>

            {{-- Konfirmasi Status Pembayaran --}}
            <div class="mb-5">
                <label for="modal_nota_stage" class="block text-xs font-semibold text-slate-700 mb-1">
                    Status Pembayaran Transaksi Ini <span class="text-red-500">*</span>
                </label>
                <select id="modal_nota_stage" class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-400 outline-none">
                    <option value="uang_muka">Uang Muka</option>
                    <option value="proses" selected>Proses</option>
                    <option value="selesai">Selesai</option>
                </select>
            </div>

            {{-- Tombol Aksi --}}
            <div class="flex gap-2">
                <button type="button" onclick="submitConfirmNotaModal()"
                    class="flex-1 py-2.5 rounded-xl bg-purple-600 hover:bg-purple-700 text-white font-semibold text-xs shadow-md shadow-purple-600/20 transition-all flex items-center justify-center gap-1.5">
                    <i class="ph ph-check-circle"></i> Konfirmasi & Catat Kas
                </button>
                <button type="button" onclick="closeConfirmNotaGroupModal()"
                    class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-600 text-xs font-semibold hover:bg-slate-200 transition-colors">
                    Batal
                </button>
            </div>

            <button type="button" onclick="closeConfirmNotaGroupModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
                <i class="ph ph-x text-xl"></i>
            </button>
        </div>
    </div>

    <script>
        let currentConfirmNotaId = null;

        function handleConfirmNotaClick(notaId, projectId, categoryId, currentStage) {
            currentConfirmNotaId = notaId;

            fetch(`{{ route('transactions.check-payment-group') }}?project_id=${projectId}&category_id=${categoryId}`, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.needs_confirmation) {
                    openConfirmNotaGroupModal(data.group, currentStage);
                } else {
                    directConfirmNota(notaId, currentStage);
                }
            })
            .catch(() => {
                directConfirmNota(notaId, currentStage);
            });
        }

        function directConfirmNota(notaId, stage) {
            const form = document.getElementById('directConfirmNotaForm');
            form.action = `/nota-merah/${notaId}/confirm`;
            document.getElementById('directNotaPaymentStage').value = stage || 'proses';
            document.getElementById('directNotaGroupAction').value = '';
            document.getElementById('directNotaGroupLabel').value = '';
            form.submit();
        }

        function openConfirmNotaGroupModal(group, currentStage) {
            document.getElementById('modal_nota_project_name').textContent  = group.project_name;
            document.getElementById('modal_nota_category_name').textContent = group.category_name;
            document.getElementById('modal_nota_total_amount').textContent  =
                'Rp ' + Number(group.total_amount).toLocaleString('id-ID', {minimumFractionDigits: 0});

            const labelRow = document.getElementById('modal_nota_label_row');
            if (group.label) {
                document.getElementById('modal_nota_label').textContent = group.label;
                labelRow.classList.remove('hidden');
            } else {
                labelRow.classList.add('hidden');
            }

            document.querySelector('input[name="modal_nota_group_action"][value="lanjutkan"]').checked = true;
            toggleNotaNewGroupLabel('lanjutkan');
            if (currentStage === 'selesai') {
                document.getElementById('modal_nota_stage').value = 'selesai';
            }
            document.getElementById('modal_nota_new_label').value = '';
            document.getElementById('modal_nota_label_error').classList.add('hidden');

            document.getElementById('confirmNotaGroupModal').classList.remove('hidden');
        }

        function closeConfirmNotaGroupModal() {
            document.getElementById('confirmNotaGroupModal').classList.add('hidden');
            currentConfirmNotaId = null;
        }

        function toggleNotaNewGroupLabel(action) {
            const section = document.getElementById('notaNewGroupLabelSection');
            const stageSelect = document.getElementById('modal_nota_stage');

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

        function submitConfirmNotaModal() {
            if (!currentConfirmNotaId) return;

            const action = document.querySelector('input[name="modal_nota_group_action"]:checked').value;
            const stage = document.getElementById('modal_nota_stage').value;
            let label = '';

            if (action === 'baru') {
                label = document.getElementById('modal_nota_new_label').value.trim();
                if (!label) {
                    document.getElementById('modal_nota_label_error').classList.remove('hidden');
                    return;
                }
            }

            const form = document.getElementById('directConfirmNotaForm');
            form.action = `/nota-merah/${currentConfirmNotaId}/confirm`;
            document.getElementById('directNotaPaymentStage').value = stage;
            document.getElementById('directNotaGroupAction').value = action;
            document.getElementById('directNotaGroupLabel').value = label;
            form.submit();
        }

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