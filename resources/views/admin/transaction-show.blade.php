@extends('layouts.admin')

@section('title', 'Detail Transaksi #' . $transaction->id . ' - SanthiGraha')
@section('page_title', 'Detail Transaksi')

@section('content')
    <div class="max-w-4xl mx-auto">

        {{-- Back + Header --}}
        <div class="mb-6">
            <a href="{{ $from === 'approvals' ? route('approvals.index') : route('transactions.index') }}"
                class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-emerald-600 transition-colors mb-4">
                <i class="ph ph-arrow-left"></i>
                {{ $from === 'approvals' ? 'Kembali ke Approval' : 'Kembali ke Daftar Transaksi' }}
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
                        'pending'  => ['color' => 'bg-amber-100 text-amber-700',   'icon' => 'ph-clock',        'label' => 'Menunggu Persetujuan'],
                        'approved' => ['color' => 'bg-emerald-100 text-emerald-700','icon' => 'ph-check-circle', 'label' => 'Disetujui'],
                        'rejected' => ['color' => 'bg-red-100 text-red-700',        'icon' => 'ph-x-circle',     'label' => 'Ditolak'],
                    ];
                    $sc = $statusConfig[$transaction->status] ?? ['color' => 'bg-slate-100 text-slate-600', 'icon' => 'ph-question', 'label' => $transaction->status];
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
                        <dd class="font-semibold text-slate-800">{{ $transaction->project->project_name ?? '-' }}</dd>
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

                    @if($transaction->type === 'pengeluaran')
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
                                    default     => $stage ?: 'Belum diisi',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-bold border {{ $stageColor }}">
                                {{ $stageLabel }}
                            </span>
                        </dd>
                    </div>
                    @endif

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

            {{-- Kolom kanan: Info Pengaju/Penyetuju + Link Kelompok --}}
            <div class="space-y-4">

                {{-- Info Pengaju & Penyetuju --}}
                <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5">
                    <h3 class="text-sm font-bold text-slate-700 mb-3 flex items-center gap-2">
                        <i class="ph ph-users text-blue-500"></i> Pengaju & Penyetuju
                    </h3>
                    <div class="space-y-3">
                        {{-- Pengaju --}}
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-sm shrink-0">
                                {{ substr($transaction->user->name ?? 'U', 0, 1) }}
                            </div>
                            <div>
                                <p class="text-xs text-slate-400">Pengaju</p>
                                <p class="text-sm font-semibold text-slate-800">{{ $transaction->user->name ?? '-' }}</p>
                            </div>
                        </div>
                        {{-- Penyetuju/Penolak --}}
                        @if($transaction->approver)
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full {{ $transaction->status === 'approved' ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-600' }} flex items-center justify-center font-bold text-sm shrink-0">
                                    {{ substr($transaction->approver->name ?? 'A', 0, 1) }}
                                </div>
                                <div>
                                    <p class="text-xs text-slate-400">{{ $transaction->status === 'approved' ? 'Disetujui oleh' : 'Diproses oleh' }}</p>
                                    <p class="text-sm font-semibold text-slate-800">{{ $transaction->approver->name }}</p>
                                </div>
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

                {{-- Info Payment Group --}}
                @if($paymentGroup)
                    <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4">
                        <div class="flex items-center gap-3">
                            <i class="ph ph-stack text-indigo-500 text-lg flex-shrink-0"></i>
                            <div class="flex-1">
                                <p class="text-sm font-bold text-indigo-700 mb-0.5">Kelompok Pembayaran #{{ $paymentGroup->id }}</p>
                                <p class="text-xs text-indigo-600">Tergabung dalam kelompok pembayaran dengan total {{ $groupTransactions->count() }} nota.</p>
                                <a href="{{ route('payment-groups.show', $paymentGroup->id) }}"
                                    class="inline-flex items-center gap-1 text-xs font-semibold text-indigo-700 underline mt-1 hover:text-indigo-900 transition-colors">
                                    <i class="ph ph-arrow-square-out"></i> Lihat Riwayat Lengkap Kelompok
                                </a>
                            </div>
                        </div>
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
                                    <div class="flex items-center gap-3">
                                        <span class="text-base font-bold text-slate-800">
                                            Rp {{ number_format($trxItem->amount, 2, ',', '.') }}
                                        </span>
                                        <form action="{{ route('transactions.destroy', $trxItem->id) }}" method="POST"
                                              id="delete-nota-form-{{ $trxItem->id }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                    onclick="confirmDelete('delete-nota-form-{{ $trxItem->id }}', 'Apakah Anda yakin ingin menghapus data nota transaksi #{{ $trxItem->id }} ini?')"
                                                    class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-red-50 text-red-600 text-xs font-semibold hover:bg-red-100 transition-colors border border-red-200"
                                                    title="Hapus Nota Ini">
                                                <i class="ph ph-trash"></i> Hapus
                                            </button>
                                        </form>
                                    </div>
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

        {{-- Banner Aksi: hanya jika status masih pending --}}
        @if($transaction->status === 'pending')
            <div class="mt-5 p-5 bg-amber-50 border border-amber-200 rounded-2xl flex items-start justify-between flex-wrap gap-4">
                <div>
                    <p class="font-bold text-amber-800 text-sm">Transaksi ini menunggu persetujuan Anda.</p>
                    <p class="text-sm text-amber-700 mt-0.5">Tinjau data di atas sebelum mengambil keputusan.</p>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <button onclick="rejectTransaction({{ $transaction->id }})"
                        class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-red-50 text-red-600 font-semibold text-sm hover:bg-red-500 hover:text-white transition-colors whitespace-nowrap border border-red-100 hover:border-red-500">
                        <i class="ph ph-x-circle"></i> Tolak
                    </button>
                    <form action="{{ route('transactions.approve', $transaction->id) }}" method="POST" class="flex items-center gap-2 flex-wrap">
                        @csrf
                        @php
                            $targetGroup = $transaction->paymentGroup ?: \App\Models\PaymentGroup::where('project_id', $transaction->project_id)->where('category_id', $transaction->category_id)->where('type', $transaction->type)->orderByDesc('id')->first();
                            $isGroupCompleted = $targetGroup && $targetGroup->payment_status === 'selesai';
                        @endphp

                        @if($isGroupCompleted)
                            <div class="flex items-center gap-2 flex-wrap">
                                <select name="payment_group_action" id="approve_group_action" onchange="toggleApproveStage(this.value)"
                                    class="px-3 py-2 rounded-xl border border-amber-300 bg-white text-slate-800 text-xs font-semibold focus:ring-2 focus:ring-amber-400 outline-none">
                                    <option value="lanjutkan">Gunakan Kelompok Sebelumnya (Lanjutkan)</option>
                                    <option value="baru">Buat Label Baru</option>
                                </select>
                                <input type="text" name="payment_group_label" id="approve_group_label" placeholder="Label Baru (cth: Tahap 2)"
                                    class="hidden px-3 py-2 rounded-xl border border-amber-300 bg-white text-slate-800 text-xs focus:ring-2 focus:ring-amber-400 outline-none">
                                <select name="payment_stage" id="approve_stage"
                                    class="px-3 py-2 rounded-xl border border-amber-300 bg-amber-50 text-amber-800 text-xs font-semibold focus:ring-2 focus:ring-amber-400 outline-none">
                                    <option value="proses" selected>Proses</option>
                                    <option value="selesai">Selesai</option>
                                </select>
                            </div>
                            <script>
                                function toggleApproveStage(val) {
                                    const labelInput = document.getElementById('approve_group_label');
                                    const stageSel   = document.getElementById('approve_stage');
                                    if (val === 'baru') {
                                        labelInput.classList.remove('hidden');
                                        labelInput.required = true;
                                        stageSel.innerHTML = `
                                            <option value="uang_muka" selected>Uang Muka</option>
                                            <option value="selesai">Selesai</option>
                                        `;
                                    } else {
                                        labelInput.classList.add('hidden');
                                        labelInput.required = false;
                                        labelInput.value = '';
                                        stageSel.innerHTML = `
                                            <option value="proses" selected>Proses</option>
                                            <option value="selesai">Selesai</option>
                                        `;
                                    }
                                }
                            </script>
                        @else
                            <div class="flex items-center gap-2">
                                <label class="text-xs font-semibold text-amber-700 whitespace-nowrap">Status Pembayaran:</label>
                                <select name="payment_stage"
                                    class="px-3 py-2 rounded-xl border border-amber-300 bg-amber-50 text-amber-800 text-xs font-semibold focus:ring-2 focus:ring-amber-400 outline-none">
                                    @if($targetGroup && $targetGroup->payment_status !== 'selesai')
                                        <option value="proses"  @selected(($transaction->payment_stage ?? 'proses') === 'proses')>Proses</option>
                                        <option value="selesai" @selected($transaction->payment_stage === 'selesai')>Selesai</option>
                                    @else
                                        <option value="uang_muka" @selected(($transaction->payment_stage ?? 'uang_muka') === 'uang_muka')>Uang Muka</option>
                                        <option value="selesai"   @selected($transaction->payment_stage === 'selesai')>Selesai</option>
                                    @endif
                                </select>
                            </div>
                        @endif
                        <button type="submit"
                            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-500 text-white font-semibold text-sm hover:bg-emerald-600 transition-colors whitespace-nowrap">
                            <i class="ph ph-check-circle"></i> Setujui
                        </button>
                    </form>
                </div>
            </div>

            {{-- Hidden Reject Form --}}
            <form id="rejectForm" method="POST" action="{{ route('transactions.reject', $transaction->id) }}" class="hidden">
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
                            document.getElementById('rejectReason').value = result.value;
                            document.getElementById('rejectForm').submit();
                        }
                    });
                }
            </script>
        @endif

        {{-- Info selesai (approved) --}}
        @if($transaction->status === 'approved')
            <div class="mt-5 bg-emerald-50 border border-emerald-200 rounded-2xl p-4">
                <div class="flex items-center gap-3">
                    <i class="ph ph-check-circle text-emerald-500 text-lg flex-shrink-0"></i>
                    <div>
                        <p class="text-sm font-bold text-emerald-700">Transaksi Disetujui & Tercatat di Buku Kas</p>
                        <p class="text-sm text-emerald-600">
                            Disetujui oleh {{ $transaction->approver->name ?? 'Admin' }}
                            {{ $transaction->updated_at ? 'pada ' . $transaction->updated_at->format('d M Y, H:i') . ' WITA' : '' }}
                        </p>
                    </div>
                </div>
            </div>
        @endif

    </div>
@endsection
