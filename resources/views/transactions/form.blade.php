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
            <p class="font-semibold mb-1">Cara Pengajuan Transaksi:</p>
            <ol class="list-decimal ml-4 space-y-0.5 text-amber-700">
                <li>Isi form ini dan upload bukti pembayaran / struk</li>
                <li>Admin akan menyetujui atau menolak pengajuan Anda</li>
                <li>Jika disetujui, transaksi akan tercatat resmi di buku kas</li>
            </ol>
        </div>
    </div>
    @endif

    {{-- Form --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
        <form action="{{ isset($transaction) ? route('transactions.update', $transaction->id) : route('transactions.store') }}"
              method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @if(isset($transaction))
                @method('PUT')
            @endif

            {{-- Tanggal Transaksi --}}
            <div>
                <label for="transaction_date" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Tanggal Transaksi <span class="text-red-500">*</span>
                </label>
                <div class="relative">
                    <i class="ph ph-calendar-blank absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg pointer-events-none"></i>
                    <input type="date" id="transaction_date" name="transaction_date"
                        value="{{ old('transaction_date', isset($transaction) ? \Carbon\Carbon::parse($transaction->transaction_date)->format('Y-m-d') : date('Y-m-d')) }}"
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
                    <option value="pemasukan"   @selected(old('type', $transaction->type ?? '') === 'pemasukan')>Pemasukan (Uang Masuk)</option>
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
                        @foreach(['Cash', 'Bank BPD', 'BRI', 'BCA'] as $method)
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
                        <p class="text-xs text-slate-400 mt-1">JPG, PNG, PDF — Maks. 5MB</p>
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
                <button type="submit"
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

<script>
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

</script>
@endsection