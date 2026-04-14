@extends(auth()->user()->role === 'admin' ? 'layouts.admin' : 'layouts.pegawai')

@section('title', isset($transaction) ? 'Edit Transaksi - SanthiGraha' : 'Tambah Transaksi - SanthiGraha')
@section('page_title', isset($transaction) ? 'Edit Data Transaksi' : 'Pengajuan Transaksi Baru')

@section('content')
    <div class="max-w-3xl mx-auto pb-10">
        <!-- Tombol Kembali -->
        <div class="mb-6 mt-2">
            <a href="{{ route('transactions.index') }}"
                class="inline-flex items-center gap-2 text-slate-500 hover:text-brand-600 transition-colors font-medium text-[15px]">
                <i class="ph ph-arrow-left text-lg"></i>
                Kembali ke Daftar Transaksi
            </a>
        </div>

        <!-- Form Container -->
        <div
            class="bg-white rounded-3xl shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100 overflow-hidden">

            <!-- Header -->
            <div class="px-8 py-6 border-b border-slate-100 bg-slate-50/50 flex items-center gap-4">
                <div class="w-12 h-12 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center shrink-0">
                    <i class="ph {{ isset($transaction) ? 'ph-pencil-simple' : 'ph-receipt' }} text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-xl font-bold text-slate-800 tracking-tight">
                        {{ isset($transaction) ? 'Perbarui Transaksi' : 'Form Entri Transaksi' }}
                    </h2>
                    <p class="text-[13px] font-medium text-slate-500 mt-0.5">
                        Pastikan informasi proyek, kategori, nominal, dan tanggal sudah tepat.
                    </p>
                </div>
            </div>

            <!-- Form Body -->
            <form
                action="{{ isset($transaction) ? route('transactions.update', $transaction->id) : route('transactions.store') }}"
                method="POST" enctype="multipart/form-data" class="p-8">
                @csrf
                @if(isset($transaction))
                    @method('PUT')
                @endif

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">

                    <!-- Transaction Date -->
                    <div>
                        <label class="block text-[13px] font-bold tracking-wide text-slate-700 uppercase mb-2">
                            Tanggal Transaksi <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <i
                                class="ph ph-calendar-blank absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                            <input type="date" name="transaction_date"
                                value="{{ old('transaction_date', isset($transaction) ? \Carbon\Carbon::parse($transaction->transaction_date)->format('Y-m-d') : date('Y-m-d')) }}"
                                required
                                class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 focus:bg-white outline-none transition-all text-slate-700">
                        </div>
                        @error('transaction_date') <span
                            class="text-xs font-semibold text-red-500 mt-1.5 block flex items-center gap-1"><i
                        class="ph ph-warning-circle"></i> {{ $message }}</span> @enderror
                    </div>

                    <!-- Transaction Type -->
                    <div>
                        <label class="block text-[13px] font-bold tracking-wide text-slate-700 uppercase mb-2">
                            Tipe Arus Kas <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <i
                                class="ph ph-arrows-left-right absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                            <select name="type" required
                                class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 focus:bg-white outline-none transition-all cursor-pointer text-slate-700 appearance-none">
                                <option value="" class="text-slate-400">-- Pilih Jenis Transaksi --</option>
                                <option value="pemasukan" {{ old('type', $transaction->type ?? '') == 'pemasukan' ? 'selected' : '' }}>Pemasukan (Uang Masuk)</option>
                                <option value="pengeluaran" {{ old('type', $transaction->type ?? '') == 'pengeluaran' ? 'selected' : '' }}>Pengeluaran (Uang Keluar)</option>
                            </select>
                            <i
                                class="ph ph-caret-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                        </div>
                        @error('type') <span
                            class="text-xs font-semibold text-red-500 mt-1.5 block flex items-center gap-1"><i
                        class="ph ph-warning-circle"></i> {{ $message }}</span> @enderror
                    </div>

                    <!-- Project ID -->
                    <div>
                        <label class="block text-[13px] font-bold tracking-wide text-slate-700 uppercase mb-2">
                            Pilih Proyek <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <i class="ph ph-folder absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                            <select name="project_id" required
                                class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 focus:bg-white outline-none transition-all cursor-pointer text-slate-700 appearance-none">
                                <option value="" class="text-slate-400">-- Pilih Proyek Terkait --</option>
                                @foreach($projects as $project)
                                    <option value="{{ $project->id }}" {{ old('project_id', $transaction->project_id ?? '') == $project->id ? 'selected' : '' }}>
                                        {{ $project->project_name }}
                                    </option>
                                @endforeach
                            </select>
                            <i
                                class="ph ph-caret-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                        </div>
                        @error('project_id') <span
                            class="text-xs font-semibold text-red-500 mt-1.5 block flex items-center gap-1"><i
                        class="ph ph-warning-circle"></i> {{ $message }}</span> @enderror
                    </div>

                    <!-- Category ID -->
                    <div>
                        <label class="block text-[13px] font-bold tracking-wide text-slate-700 uppercase mb-2">
                            Kategori Biaya <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <i class="ph ph-tag absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-lg"></i>
                            <select name="category_id" required
                                class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 focus:bg-white outline-none transition-all cursor-pointer text-slate-700 appearance-none">
                                <option value="" class="text-slate-400">-- Pilih Kategori --</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $transaction->category_id ?? '') == $category->id ? 'selected' : '' }}>
                                        {{ $category->category_name }}
                                    </option>
                                @endforeach
                            </select>
                            <i
                                class="ph ph-caret-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                        </div>
                        @error('category_id') <span
                            class="text-xs font-semibold text-red-500 mt-1.5 block flex items-center gap-1"><i
                        class="ph ph-warning-circle"></i> {{ $message }}</span> @enderror
                    </div>
                </div>

                <!-- Amount -->
                <div class="mb-6">
                    <label class="block text-[13px] font-bold tracking-wide text-slate-700 uppercase mb-2">
                        Nominal Transaksi (Rp) <span class="text-red-500">*</span>
                    </label>
                    <div class="relative flex items-center">
                        <div
                            class="absolute left-0 top-0 bottom-0 px-4 bg-slate-100 border border-slate-200 border-r-0 rounded-l-xl flex items-center justify-center">
                            <span class="text-slate-500 font-bold text-[15px]">Rp</span>
                        </div>
                        <input type="number" name="amount" min="0" step="1"
                            value="{{ old('amount', isset($transaction) ? intval($transaction->amount) : '') }}" required
                            placeholder="Contoh: 1500000"
                            class="w-full pl-[60px] pr-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-lg font-bold text-slate-800 placeholder:text-slate-300 placeholder:font-medium focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 focus:bg-white outline-none transition-all">
                    </div>
                    <!-- Mini hint -->
                    <p class="text-[11px] font-medium tracking-wide text-slate-400 mt-1.5 uppercase ml-1 block"
                        id="amount-hint">Otomatis diformat ribuan saat disubmit</p>
                    @error('amount') <span
                        class="text-xs font-semibold text-red-500 mt-1.5 block flex items-center gap-1"><i
                    class="ph ph-warning-circle"></i> {{ $message }}</span> @enderror
                </div>

                <!-- Payment Method -->
                <div class="mb-6">
                    <label class="block text-[13px] font-bold tracking-wide text-slate-700 uppercase mb-2">
                        Metode Transfer <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <select name="payment_method" required
                            class="w-full px-4 py-3.5 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 focus:bg-white outline-none transition-all appearance-none cursor-pointer">
                            <option value="">-- Pilih Metode Transaksi --</option>
                            <option value="Cash" {{ old('payment_method', $transaction->payment_method ?? '') == 'Cash' ? 'selected' : '' }}>Cash (Tunai)</option>
                            <option value="Bank BPD" {{ old('payment_method', $transaction->payment_method ?? '') == 'Bank BPD' ? 'selected' : '' }}>Bank BPD</option>
                            <option value="BRI" {{ old('payment_method', $transaction->payment_method ?? '') == 'BRI' ? 'selected' : '' }}>Bank BRI</option>
                            <option value="BCA" {{ old('payment_method', $transaction->payment_method ?? '') == 'BCA' ? 'selected' : '' }}>Bank BCA</option>
                        </select>
                        <i
                            class="ph ph-caret-down absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"></i>
                    </div>
                    @error('payment_method') <span
                        class="text-xs font-semibold text-red-500 mt-1.5 block flex items-center gap-1"><i
                    class="ph ph-warning-circle"></i> {{ $message }}</span> @enderror
                </div>

                <!-- Receipt Photo (Upload) -->
                <div class="mb-6">
                    <label class="block text-[13px] font-bold tracking-wide text-slate-700 uppercase mb-2">
                        Bukti Transaksi <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <input type="file" name="receipt_photo" id="receipt_photo" accept="image/*,.pdf"
                            capture="environment" {{ isset($transaction) && $transaction->receipt_photo ? '' : 'required' }}
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 focus:bg-white outline-none transition-all text-slate-700 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-brand-50 file:text-brand-600 hover:file:bg-brand-100 cursor-pointer">
                        <p id="compress_hint"
                            class="text-xs text-brand-600 mt-2 font-bold hidden flex items-center gap-1.5">
                            <i class="ph ph-check-circle"></i> Ukuran foto berhasil diperkecil secara otomatis!
                        </p>
                    </div>
                    @if(isset($transaction) && $transaction->receipt_photo)
                        <div class="mt-3 flex items-center gap-3">
                            <span class="text-xs font-medium text-slate-500">File tersimpan:</span>
                            <a href="{{ asset('storage/' . $transaction->receipt_photo) }}" target="_blank"
                                class="px-3 py-1.5 bg-indigo-50 text-indigo-600 rounded-lg text-xs font-bold flex items-center gap-1.5 hover:bg-indigo-100 transition-colors">
                                <i class="ph ph-link"></i> Buka Gambar/PDF Asli
                            </a>
                        </div>
                    @endif
                    <p class="text-[11px] font-medium tracking-wide text-slate-400 mt-1.5 uppercase ml-1 block">Format: JPG,
                        PNG, PDF (Maks 5MB)</p>
                    @error('receipt_photo') <span
                        class="text-xs font-semibold text-red-500 mt-1.5 block flex items-center gap-1"><i
                    class="ph ph-warning-circle"></i> {{ $message }}</span> @enderror
                </div>

                <!-- Description -->
                <div class="mb-8">
                    <label class="block text-[13px] font-bold tracking-wide text-slate-700 uppercase mb-2">
                        Keterangan Tambahan / Deskripsi
                    </label>
                    <div class="relative">
                        <i class="ph ph-text-aa absolute left-3 top-3.5 text-slate-400 text-lg"></i>
                        <textarea name="description" rows="3"
                            placeholder="Tuliskan detail spesifik dari barang/jasa atau sumber pemasukan..."
                            class="w-full pl-10 pr-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm font-medium focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 focus:bg-white outline-none transition-all text-slate-700">{{ old('description', $transaction->description ?? '') }}</textarea>
                    </div>
                    @error('description') <span
                        class="text-xs font-semibold text-red-500 mt-1.5 block flex items-center gap-1"><i
                    class="ph ph-warning-circle"></i> {{ $message }}</span> @enderror
                </div>

                <!-- Submit Buttons -->
                <div class="flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                    <a href="{{ route('transactions.index') }}"
                        class="px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 text-[14px] font-bold tracking-wide rounded-xl transition-colors">
                        Batalkan
                    </a>
                    <button type="submit" id="submitBtn"
                        class="px-7 py-3 bg-brand-600 hover:bg-brand-700 text-white text-[14px] font-bold tracking-wide rounded-xl shadow-[0_4px_12px_rgba(99,102,241,0.3)] hover:shadow-[0_6px_16px_rgba(99,102,241,0.4)] hover:-translate-y-0.5 transition-all flex items-center justify-center gap-2">
                        <i class="ph {{ isset($transaction) ? 'ph-floppy-disk' : 'ph-paper-plane-tilt' }} text-xl"></i>
                        {{ isset($transaction) ? 'Simpan Perubahan' : 'Submit Transaksi' }}
                    </button>
                </div>

            </form>
        </div>
    </div>
@endsection