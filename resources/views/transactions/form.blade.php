@extends(auth()->user()->role === 'admin' ? 'layouts.admin' : 'layouts.pegawai')

@section('title', isset($transaction) ? 'Edit Transaksi - SanthiGraha' : 'Ajukan Transaksi - SanthiGraha')
@section('page_title', isset($transaction) ? 'Edit Transaksi' : 'Ajukan Transaksi')

@section('content')
<div class="max-w-2xl mx-auto">

    {{-- Header --}}
    <div class="mb-6">
        <a href="{{ route('transactions.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-emerald-600 transition-colors mb-4">
            <i class="ph ph-arrow-left"></i> Kembali ke Daftar
        </a>
        <h2 class="text-lg font-bold text-slate-800">
            {{ isset($transaction) ? 'Edit Transaksi #' . $transaction->id : 'Form Pengajuan Transaksi' }}
        </h2>
        <p class="text-sm text-slate-500 mt-1">
            {{ isset($transaction) ? 'Perbarui data transaksi dan kirim ulang untuk persetujuan Admin.' : 'Isi data transaksi dengan lengkap dan lampirkan bukti pembayaran.' }}
        </p>
    </div>

    {{-- Banner Alasan Penolakan (saat edit & status rejected) --}}
    @if(isset($transaction) && $transaction->status === 'rejected' && isset($transaction->rejections) && $transaction->rejections->count() > 0)
    <div class="bg-red-50 border border-red-200 rounded-xl p-4 mb-6 flex gap-3">
        <i class="ph ph-x-circle text-red-500 text-xl flex-shrink-0 mt-0.5"></i>
        <div class="text-sm text-red-800">
            <p class="font-semibold mb-1">Alasan Penolakan dari Admin:</p>
            <p class="text-red-700 leading-relaxed">{{ $transaction->rejections->last()->reason }}</p>
        </div>
    </div>
    @endif

    {{-- Info Banner (saat create) --}}
    @if(!isset($transaction))
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 flex gap-3">
        <i class="ph ph-info text-amber-500 text-xl flex-shrink-0 mt-0.5"></i>
        <div class="text-sm text-amber-800">
            @if(auth()->user()->role === 'admin')
            <p class="font-semibold mb-1">Informasi:</p>
            <p class="text-amber-700">Isi form ini dan upload bukti pembayaran / struk. Transaksi akan langsung tercatat resmi di buku kas.</p>
            @else
            <p class="font-semibold mb-1">Cara Pengajuan Transaksi:</p>
            <ol class="list-decimal ml-4 space-y-0.5 text-amber-700">
                <li>Isi form ini dan upload bukti pembayaran / struk</li>
                <li>Admin akan menyetujui atau menolak pengajuan Anda</li>
                <li>Jika disetujui, transaksi akan tercatat resmi di buku kas</li>
            </ol>
            @endif
        </div>
    </div>
    @endif

    {{-- Form --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
        <form action="{{ isset($transaction) ? route('transactions.update', $transaction->id) : route('transactions.store') }}"
              method="POST" enctype="multipart/form-data" class="space-y-6" id="transactionForm">
            @csrf
            @if(isset($transaction))
                @method('PUT')
            @endif

            {{-- Hidden inputs untuk Payment Group action (diisi oleh JS modal) --}}
            <input type="hidden" name="payment_group_action" id="payment_group_action" value="">
            <input type="hidden" name="payment_group_label"  id="payment_group_label"  value="">

            {{-- Tanggal Nota --}}
            <div>
                <label for="transaction_date" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Tanggal Nota <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <i class="ph ph-calendar-blank absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg pointer-events-none"></i>
                    <input type="date" id="transaction_date" name="transaction_date"
                        value="{{ old('transaction_date', isset($transaction) ? \Carbon\Carbon::parse($transaction->transaction_date)->format('Y-m-d') : date('Y-m-d')) }}"
                        max="{{ date('Y-m-d') }}"
                        class="w-full pl-10 pr-4 py-3 rounded-xl border @error('transaction_date') border-red-400 bg-red-50 @else border-slate-200 @enderror text-sm focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 outline-none">
                </div>
                @error('transaction_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Tipe Transaksi --}}
            <div>
                <label for="type" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Tipe Arus Kas <span class="text-red-500">*</span>
                </label>
                <select id="type" name="type"
                    class="w-full px-4 py-3 rounded-xl border @error('type') border-red-400 bg-red-50 @else border-slate-200 @enderror text-sm focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 outline-none">
                    <option value="">-- Pilih Tipe Transaksi --</option>
                    @if(auth()->user()->role === 'admin')
                    <option value="pemasukan"   @selected(old('type', $transaction->type ?? '') === 'pemasukan')>Pemasukan (Uang Masuk)</option>
                    @endif
                    <option value="pengeluaran" @selected(old('type', $transaction->type ?? '') === 'pengeluaran')>Pengeluaran (Uang Keluar)</option>
                </select>
                @error('type') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Proyek & Kategori --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="project_id" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Proyek <span class="text-red-500">*</span>
                    </label>
                    <select id="project_id" name="project_id"
                        class="w-full px-4 py-3 rounded-xl border @error('project_id') border-red-400 bg-red-50 @else border-slate-200 @enderror text-sm focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 outline-none">
                        <option value="">-- Pilih Proyek --</option>
                        @foreach($projects as $project)
                            <option value="{{ $project->id }}" @selected(old('project_id', $transaction->project_id ?? '') == $project->id)>
                                {{ $project->project_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('project_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="category_id" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Kategori <span class="text-red-500">*</span>
                    </label>
                    <select id="category_id" name="category_id"
                        class="w-full px-4 py-3 rounded-xl border @error('category_id') border-red-400 bg-red-50 @else border-slate-200 @enderror text-sm focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 outline-none">
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $transaction->category_id ?? '') == $category->id)>
                                {{ $category->category_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Status Pembayaran (untuk Pemasukan & Pengeluaran) --}}
            <div id="payment-stage-field">
                {{-- Box Info saat Kelompok Sebelumnya Selesai (Khusus Pegawai) --}}
                <div id="payment-stage-completed-info" class="hidden bg-amber-50 border border-amber-200 rounded-xl p-4 text-xs text-amber-800 flex items-start gap-3 mb-2">
                    <i class="ph ph-info text-lg text-amber-600 shrink-0 mt-0.5"></i>
                    <div>
                        <span class="font-bold text-amber-900 text-sm block mb-0.5">Kelompok Pembayaran Sebelumnya Selesai</span>
                        <p class="text-amber-700 leading-relaxed">Kelompok pembayaran untuk proyek & kategori ini sebelumnya sudah selesai. Status pembayaran dan penentuan kelompok baru akan divalidasi oleh Admin saat proses persetujuan (approval).</p>
                    </div>
                </div>

                {{-- Input Select Status Pembayaran --}}
                <div id="payment-stage-select-wrapper">
                    <label for="payment_stage" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Status Pembayaran <span class="text-red-500">*</span>
                        <span class="ml-1 text-xs font-normal text-slate-400">
                            @if(auth()->user()->role === 'admin')
                                (Uang Muka / Proses / Selesai)
                            @else
                                (Uang Muka / Proses)
                            @endif
                        </span>
                    </label>
                    <select id="payment_stage" name="payment_stage"
                        class="w-full px-4 py-3 rounded-xl border @error('payment_stage') border-red-400 bg-red-50 @else border-slate-200 @enderror text-sm focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 outline-none"
                        required>
                        <option value="">-- Pilih Status Pembayaran --</option>
                        <option value="uang_muka" @selected(old('payment_stage', $transaction->payment_stage ?? 'uang_muka') === 'uang_muka')>Uang Muka</option>
                        @if(auth()->user()->role === 'admin')
                        <option value="selesai"   @selected(old('payment_stage', $transaction->payment_stage ?? '') === 'selesai')>Selesai</option>
                        @endif
                    </select>
                    <p id="payment-stage-helper" class="text-xs text-indigo-600 font-medium mt-1.5 hidden flex items-center gap-1">
                        <i class="ph ph-info"></i>
                        @if(auth()->user()->role === 'admin')
                            Pembayaran lanjutan terdeteksi — silakan pilih Proses atau Selesai.
                        @else
                            Pembayaran lanjutan terdeteksi — silakan pilih Proses.
                        @endif
                    </p>
                    @error('payment_stage') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Nominal & Metode --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="amount" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Nominal <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 font-medium text-sm">Rp</span>
                        <input type="text" id="amount" name="amount" inputmode="decimal" data-rupiah
                            value="{{ old('amount', isset($transaction) ? $transaction->amount : '') }}"
                            placeholder="0"
                            class="w-full pl-10 pr-4 py-3 rounded-xl border @error('amount') border-red-400 bg-red-50 @else border-slate-200 @enderror text-sm focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 outline-none">
                    </div>
                    @error('amount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="payment_method" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Metode Pembayaran <span class="text-red-500">*</span>
                    </label>
                    <select id="payment_method" name="payment_method"
                        class="w-full px-4 py-3 rounded-xl border @error('payment_method') border-red-400 bg-red-50 @else border-slate-200 @enderror text-sm focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 outline-none">
                        <option value="">-- Pilih Metode --</option>
                        @php
                            $defaultMethods = ['Cash', 'Bank BPD', 'BRI', 'BCA'];
                            $currentMethod = old('payment_method', $transaction->payment_method ?? '');
                            if ($currentMethod && !in_array($currentMethod, $defaultMethods)) {
                                $defaultMethods[] = $currentMethod;
                            }
                        @endphp
                        @foreach($defaultMethods as $method)
                            <option value="{{ $method }}" @selected(old('payment_method', $transaction->payment_method ?? '') === $method)>{{ $method }}</option>
                        @endforeach
                    </select>
                    @error('payment_method') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Deskripsi --}}
            <div>
                <label for="description" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Keterangan / Deskripsi
                </label>
                <textarea id="description" name="description" rows="3"
                    placeholder="Tuliskan detail spesifik dari barang/jasa atau sumber pemasukan (opsional)..."
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 outline-none resize-none">{{ old('description', $transaction->description ?? '') }}</textarea>
                @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Upload Bukti Transaksi --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Bukti Transaksi / Struk
                    @if(!isset($transaction) || !$transaction->receipt_photo)
                        <span class="text-red-500">*</span>
                    @else
                        <span class="text-slate-400 font-normal">(opsional — kosongkan jika tidak ingin mengganti)</span>
                    @endif
                </label>

                {{-- Preview foto lama saat edit --}}
                @if(isset($transaction) && $transaction->receipt_photo)
                <div class="mb-3 flex items-center gap-3">
                    <span class="text-xs font-medium text-slate-500">Bukti saat ini:</span>
                    @if(str_ends_with(strtolower($transaction->receipt_photo), '.pdf'))
                        <a href="{{ asset('storage/' . $transaction->receipt_photo) }}" target="_blank"
                           class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-red-50 text-red-600 font-medium text-xs hover:bg-red-100 transition-colors border border-red-100">
                            <i class="ph ph-file-pdf"></i> Lihat PDF
                        </a>
                    @else
                        <a href="{{ asset('storage/' . $transaction->receipt_photo) }}" target="_blank">
                            <img src="{{ asset('storage/' . $transaction->receipt_photo) }}" alt="Bukti Transaksi"
                                 class="h-16 rounded-lg object-cover border border-slate-200 hover:opacity-80 transition-opacity">
                        </a>
                    @endif
                </div>
                @endif

                <div class="border-2 border-dashed @error('receipt_photo') border-red-400 bg-red-50 @else border-slate-300 @enderror rounded-xl p-6 text-center hover:border-emerald-400 transition-colors relative">
                    <input type="file" id="receipt_photo" name="receipt_photo"
                        accept=".jpg,.jpeg,.png,.pdf"
                        {{ (!isset($transaction) || !$transaction->receipt_photo) ? 'required' : '' }}
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                        onchange="previewFile(this, 'preview_receipt')">
                    <div id="upload_receipt_placeholder">
                        <i class="ph ph-file-arrow-up text-4xl text-slate-300 mb-2"></i>
                        <p class="text-sm text-slate-500 font-medium">Klik atau drag & drop file di sini</p>
                        <p class="text-xs text-slate-400 mt-1">JPG, PNG, PDF — Otomatis dikompres jika >5MB</p>
                    </div>
                    <div id="preview_receipt" class="hidden mt-2">
                        <img id="preview_receipt_img" src="" alt="Preview" class="h-32 mx-auto rounded-lg object-cover border border-slate-200">
                        <p id="preview_receipt_name" class="text-xs text-slate-500 mt-2"></p>
                    </div>
                </div>
                @error('receipt_photo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Submit --}}
            <div class="flex gap-3 pt-2">
                <button type="submit" id="submitBtn"
                    class="flex-1 py-3 rounded-xl bg-emerald-500 text-white font-semibold text-sm hover:bg-emerald-600 hover:shadow-lg hover:shadow-emerald-500/20 transition-all flex items-center justify-center gap-2">
                    <i class="ph ph-paper-plane-tilt text-lg"></i>
                    {{ isset($transaction) ? 'Kirim Ulang Pengajuan' : 'Kirim Pengajuan' }}
                </button>
                <a href="{{ route('transactions.index') }}"
                    class="px-6 py-3 rounded-xl bg-slate-100 text-slate-600 font-semibold text-sm hover:bg-slate-200 transition-colors text-center">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ================================================================ --}}
{{-- MODAL KONFIRMASI PAYMENT GROUP (muncul saat status Selesai) --}}
{{-- ================================================================ --}}
@if(auth()->user()->role === 'admin')
{{-- Modal Konfirmasi Payment Group (Saat grup lama sudah selesai) --}}
<div id="paymentGroupModal"
    class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm hidden">
    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full mx-4 p-6 relative animate-in fade-in zoom-in duration-200">
        {{-- Icon & Judul --}}
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center shrink-0">
                <i class="ph ph-warning-circle text-2xl"></i>
            </div>
            <div>
                <h3 class="font-bold text-slate-800 text-base">Kelompok Pembayaran Ditemukan</h3>
                <p class="text-xs text-slate-500">Kombinasi proyek & kategori ini sebelumnya sudah selesai.</p>
            </div>
        </div>

        {{-- Info Group Sebelumnya --}}
        <div class="bg-slate-50 rounded-xl p-4 mb-4 text-xs space-y-1.5 border border-slate-100">
            <div class="flex justify-between">
                <span class="text-slate-500">Proyek:</span>
                <span class="font-semibold text-slate-700" id="modal_project_name">-</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-500">Kategori:</span>
                <span class="font-semibold text-slate-700" id="modal_category_name">-</span>
            </div>
            <div class="flex justify-between" id="modal_label_row">
                <span class="text-slate-500">Label Sebelumnya:</span>
                <span class="font-semibold text-slate-700" id="modal_label">-</span>
            </div>
            <div class="flex justify-between border-t border-slate-200 pt-1.5 mt-1.5">
                <span class="text-slate-500">Total Sebelumnya:</span>
                <span class="font-bold text-emerald-600" id="modal_total_amount">-</span>
            </div>
        </div>

        {{-- Pertanyaan / Form --}}
        <div id="modalChoiceSection">
            <p class="text-sm text-slate-700 font-medium mb-4">
                Apakah transaksi baru ini ingin digabungkan ke kelompok sebelumnya, atau membuat kelompok pembayaran baru?
            </p>
        </div>

        {{-- Input Label Baru (hidden default) --}}
        <div id="newGroupLabelSection" class="hidden mb-4">
            <label class="block text-xs font-semibold text-slate-700 mb-1">
                Label Kelompok Baru <span class="text-red-500">*</span>
            </label>
            <input type="text" id="new_group_label_input" placeholder="Contoh: Tahap 2, Perbaikan Lanjutan"
                class="w-full px-3.5 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-400 outline-none">
            <p class="text-[11px] text-red-500 mt-1 hidden" id="label_error">Label kelompok baru wajib diisi.</p>
        </div>

        {{-- Tombol Aksi --}}
        <div class="flex gap-2" id="modalButtonsYesNo">
            <button type="button" onclick="confirmPaymentGroup('lanjutkan')"
                class="flex-1 py-2.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-semibold text-xs transition-colors">
                Lanjutkan Kelompok Ini
            </button>
            <button type="button" onclick="showNewGroupForm()"
                class="flex-1 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-semibold text-xs shadow-md shadow-emerald-500/20 transition-all">
                Buat Kelompok Baru
            </button>
        </div>

        <div class="flex gap-2 hidden" id="modalButtonsConfirmNew">
            <button type="button" onclick="confirmPaymentGroup('baru')"
                class="flex-1 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-white font-semibold text-xs shadow-md shadow-emerald-500/20 transition-all">
                Simpan & Gunakan Kelompok Baru
            </button>
            <button type="button" onclick="resetModalToChoice()"
                class="px-4 py-2.5 rounded-xl bg-slate-100 text-slate-600 text-sm hover:bg-slate-200 transition-colors">
                Kembali
            </button>
        </div>

        <button type="button" onclick="closePaymentGroupModal()" class="absolute top-4 right-4 text-slate-400 hover:text-slate-600">
            <i class="ph ph-x text-xl"></i>
        </button>
    </div>
</div>
@endif

<script>
    const isAdmin = {{ auth()->user()->role === 'admin' ? 'true' : 'false' }};

    // ─────────────────────────────────────────────
    // File preview
    // ─────────────────────────────────────────────
    function previewFile(input, previewId) {
        const file = input.files[0];
        if (!file) return;
        const preview = document.getElementById(previewId);
        const placeholder = document.getElementById('upload_receipt_placeholder');
        const nameEl = document.getElementById('preview_receipt_name');
        const imgEl = document.getElementById('preview_receipt_img');

        if (file.type === 'application/pdf') {
            preview.classList.remove('hidden');
            placeholder.classList.add('hidden');
            imgEl.src = '';
            imgEl.classList.add('hidden');
            nameEl.textContent = '📄 ' + file.name;
        } else {
            const reader = new FileReader();
            reader.onload = (e) => {
                preview.classList.remove('hidden');
                placeholder.classList.add('hidden');
                imgEl.classList.remove('hidden');
                imgEl.src = e.target.result;
                nameEl.textContent = file.name;
            };
            reader.readAsDataURL(file);
        }
    }

    // ─────────────────────────────────────────────
    // Show/hide payment_stage field saat tipe berubah
    // ─────────────────────────────────────────────
    const typeSelect  = document.getElementById('type');
    const stageField  = document.getElementById('payment-stage-field');
    const stageSelect = document.getElementById('payment_stage');

    function togglePaymentStage() {
        // Tampilkan untuk pengeluaran, atau (admin) untuk pemasukan
        const isEligible = typeSelect.value === 'pengeluaran' || (isAdmin && typeSelect.value === 'pemasukan');
        stageField.classList.toggle('hidden', !isEligible);
        stageSelect.required = isEligible;
        
        const actionInput = document.getElementById('payment_group_action');
        const labelInput  = document.getElementById('payment_group_label');
        if (actionInput) actionInput.value = '';
        if (labelInput)  labelInput.value  = '';

        checkPaymentGroupIfNeeded();
    }

    typeSelect.addEventListener('change', togglePaymentStage);
    togglePaymentStage();

    // ─────────────────────────────────────────────
    // AJAX: cek Payment Group saat proyek + kategori dipilih
    // ─────────────────────────────────────────────
    const projectSel  = document.getElementById('project_id');
    const categorySel = document.getElementById('category_id');
    let pendingGroupData = null;

    function updatePaymentStageOptions(hasActiveGroup) {
        const helperText = document.getElementById('payment-stage-helper');
        const currentValue = stageSelect.value;

        if (hasActiveGroup) {
            // Transaksi Lanjutan: Opsi = 'Proses' (+ 'Selesai' untuk Admin), TIDAK ADA 'Uang Muka'
            if (isAdmin) {
                stageSelect.innerHTML = `
                    <option value="">-- Pilih Status Pembayaran --</option>
                    <option value="proses" ${currentValue === 'proses' || !currentValue || currentValue === 'uang_muka' ? 'selected' : ''}>Proses</option>
                    <option value="selesai" ${currentValue === 'selesai' ? 'selected' : ''}>Selesai</option>
                `;
            } else {
                stageSelect.innerHTML = `
                    <option value="proses" selected>Proses</option>
                `;
            }
            if (helperText) {
                helperText.innerHTML = '<i class="ph ph-info"></i> ' + (isAdmin ? 'Pembayaran lanjutan terdeteksi — silakan pilih Proses atau Selesai.' : 'Pembayaran lanjutan terdeteksi — status otomatis Proses.');
                helperText.classList.remove('hidden');
            }
        } else {
            // Transaksi Baru: Opsi = 'Uang Muka' (+ 'Selesai' untuk Admin), TIDAK ADA 'Proses'
            if (isAdmin) {
                stageSelect.innerHTML = `
                    <option value="">-- Pilih Status Pembayaran --</option>
                    <option value="uang_muka" ${currentValue === 'uang_muka' || !currentValue || currentValue === 'proses' ? 'selected' : ''}>Uang Muka</option>
                    <option value="selesai" ${currentValue === 'selesai' ? 'selected' : ''}>Selesai</option>
                `;
            } else {
                stageSelect.innerHTML = `
                    <option value="uang_muka" selected>Uang Muka</option>
                `;
            }
            if (helperText) {
                helperText.classList.add('hidden');
            }
        }
    }

    function checkPaymentGroupIfNeeded() {
        const projectId  = projectSel.value;
        const categoryId = categorySel.value;
        const trxType    = typeSelect.value || 'pengeluaran';

        if (!projectId || !categoryId) {
            updatePaymentStageOptions(false);
            return;
        }

        const actionInput = document.getElementById('payment_group_action');
        const labelInput  = document.getElementById('payment_group_label');
        if (actionInput) actionInput.value = '';
        if (labelInput)  labelInput.value  = '';

        fetch(`{{ route('transactions.check-payment-group') }}?project_id=${projectId}&category_id=${categoryId}&type=${trxType}`, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            const completedInfo = document.getElementById('payment-stage-completed-info');
            const selectWrapper = document.getElementById('payment-stage-select-wrapper');

            if (!isAdmin && data.is_completed) {
                // Pegawai: jika kelompok sebelumnya sudah selesai, sembunyikan dropdown status!
                if (completedInfo) completedInfo.classList.remove('hidden');
                if (selectWrapper) selectWrapper.classList.add('hidden');
                stageSelect.required = false;
                stageSelect.value = '';
            } else {
                if (completedInfo) completedInfo.classList.add('hidden');
                if (selectWrapper) selectWrapper.classList.remove('hidden');
                stageSelect.required = true;

                if (data.has_active_group) {
                    updatePaymentStageOptions(true);
                } else {
                    updatePaymentStageOptions(false);
                }
            }

            // Pop-up modal konfirmasi hanya untuk Admin
            if (isAdmin && data.needs_confirmation) {
                pendingGroupData = data.group;
                openPaymentGroupModal(data.group);
            } else {
                pendingGroupData = null;
            }
        })
        .catch(() => {});
    }

    projectSel.addEventListener('change',  checkPaymentGroupIfNeeded);
    categorySel.addEventListener('change', checkPaymentGroupIfNeeded);

    @if(auth()->user()->role === 'admin')
    // ─────────────────────────────────────────────
    // Modal logic (Khusus Admin)
    // ─────────────────────────────────────────────
    function openPaymentGroupModal(group) {
        document.getElementById('modal_project_name').textContent  = group.project_name;
        document.getElementById('modal_category_name').textContent = group.category_name;
        document.getElementById('modal_total_amount').textContent  =
            'Rp ' + Number(group.total_amount).toLocaleString('id-ID', {minimumFractionDigits: 0});

        const labelRow = document.getElementById('modal_label_row');
        if (group.label) {
            document.getElementById('modal_label').textContent = group.label;
            labelRow.classList.remove('hidden');
        } else {
            labelRow.classList.add('hidden');
        }
        resetModalToChoice();
        document.getElementById('paymentGroupModal').classList.remove('hidden');
    }

    function closePaymentGroupModal() {
        document.getElementById('paymentGroupModal').classList.add('hidden');
    }

    function showNewGroupForm() {
        document.getElementById('modalButtonsYesNo').classList.add('hidden');
        document.getElementById('newGroupLabelSection').classList.remove('hidden');
        document.getElementById('modalButtonsConfirmNew').classList.remove('hidden');
    }

    function resetModalToChoice() {
        document.getElementById('modalButtonsYesNo').classList.remove('hidden');
        document.getElementById('newGroupLabelSection').classList.add('hidden');
        document.getElementById('modalButtonsConfirmNew').classList.add('hidden');
        document.getElementById('new_group_label_input').value = '';
        document.getElementById('label_error').classList.add('hidden');
    }

    function confirmPaymentGroup(action) {
        const stageSelect = document.getElementById('payment_stage');
        const helperText = document.getElementById('payment-stage-helper');

        if (action === 'baru') {
            const label = document.getElementById('new_group_label_input').value.trim();
            if (!label) {
                document.getElementById('label_error').classList.remove('hidden');
                return;
            }
            document.getElementById('payment_group_label').value = label;

            // Buat Kelompok Baru → Hanya Uang Muka dan Selesai
            stageSelect.innerHTML = `
                <option value="">-- Pilih Status Pembayaran --</option>
                <option value="uang_muka" selected>Uang Muka</option>
                <option value="selesai">Selesai</option>
            `;
            if (helperText) helperText.classList.add('hidden');
        } else {
            // Lanjutkan Kelompok Lama → Hanya Proses dan Selesai
            stageSelect.innerHTML = `
                <option value="">-- Pilih Status Pembayaran --</option>
                <option value="proses" selected>Proses</option>
                <option value="selesai">Selesai</option>
            `;
            if (helperText) {
                helperText.innerHTML = '<i class="ph ph-info"></i> Pembayaran lanjutan terdeteksi — silakan pilih Proses atau Selesai.';
                helperText.classList.remove('hidden');
            }
        }
        document.getElementById('payment_group_action').value = action;
        closePaymentGroupModal();
    }
    @endif
</script>
@endsection