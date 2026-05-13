@extends('layouts.pegawai')

@section('title', 'Ajukan Nota Merah - SanthiGraha')
@section('page_title', 'Ajukan Nota Merah')

@section('content')
<div class="max-w-2xl mx-auto">

    {{-- Header --}}
    <div class="mb-6">
        <a href="{{ route('nota-merah.index') }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-emerald-600 transition-colors mb-4">
            <i class="ph ph-arrow-left"></i> Kembali ke Daftar
        </a>
        <h2 class="text-lg font-bold text-slate-800">Form Pengajuan Nota Merah</h2>
        <p class="text-sm text-slate-500 mt-1">Isi data kebutuhan belanja dan lampirkan nota/bukti kebutuhan sebelum melakukan pembelian.</p>
    </div>

    {{-- Alur Info Banner --}}
    <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 flex gap-3">
        <i class="ph ph-info text-amber-500 text-xl flex-shrink-0 mt-0.5"></i>
        <div class="text-sm text-amber-800">
            <p class="font-semibold mb-1">Cara Kerja Nota Merah:</p>
            <ol class="list-decimal ml-4 space-y-0.5 text-amber-700">
                <li>Isi form ini dan upload foto nota merah / RAB / bukti kebutuhan</li>
                <li>Admin akan menyetujui atau menolak pengajuan Anda</li>
                <li>Jika disetujui, lakukan pembelian dan upload bukti struk/kwitansi</li>
                <li>Admin konfirmasi → transaksi tercatat resmi di buku kas</li>
            </ol>
        </div>
    </div>

    {{-- Form --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
        <form action="{{ route('nota-merah.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- Proyek --}}
            <div>
                <label for="project_id" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Proyek <span class="text-red-500">*</span>
                </label>
                <select id="project_id" name="project_id"
                    class="w-full px-4 py-3 rounded-xl border @error('project_id') border-red-400 bg-red-50 @else border-slate-200 @enderror text-sm focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 outline-none">
                    <option value="">-- Pilih Proyek --</option>
                    @foreach($projects as $project)
                        <option value="{{ $project->id }}" @selected(old('project_id') == $project->id)>
                            {{ $project->project_name }}
                        </option>
                    @endforeach
                </select>
                @error('project_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Kategori --}}
            <div>
                <label for="category_id" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Kategori <span class="text-red-500">*</span>
                </label>
                <select id="category_id" name="category_id"
                    class="w-full px-4 py-3 rounded-xl border @error('category_id') border-red-400 bg-red-50 @else border-slate-200 @enderror text-sm focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 outline-none">
                    <option value="">-- Pilih Kategori --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                            {{ $category->category_name }}
                        </option>
                    @endforeach
                </select>
                @error('category_id') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Deskripsi --}}
            <div>
                <label for="description" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Keterangan Kebutuhan
                </label>
                <textarea id="description" name="description" rows="3"
                    placeholder="Jelaskan keperluan belanja ini (opsional)..."
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 outline-none resize-none">{{ old('description') }}</textarea>
                @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Nominal & Metode --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="amount" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Nominal Pengajuan <span class="text-red-500">*</span>
                    </label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 font-medium text-sm">Rp</span>
                        <input type="number" id="amount" name="amount" min="0" step="any"
                            value="{{ old('amount') }}"
                            placeholder="0"
                            class="w-full pl-10 pr-4 py-3 rounded-xl border @error('amount') border-red-400 bg-red-50 @else border-slate-200 @enderror text-sm focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 outline-none">
                    </div>
                    @error('amount') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="payment_method" class="block text-sm font-semibold text-slate-700 mb-1.5">
                        Metode Pencairan <span class="text-red-500">*</span>
                    </label>
                    <select id="payment_method" name="payment_method"
                        class="w-full px-4 py-3 rounded-xl border @error('payment_method') border-red-400 bg-red-50 @else border-slate-200 @enderror text-sm focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 outline-none">
                        <option value="">-- Pilih Metode --</option>
                        @foreach(['Cash', 'Bank BPD', 'BRI', 'BCA'] as $method)
                            <option value="{{ $method }}" @selected(old('payment_method') === $method)>{{ $method }}</option>
                        @endforeach
                    </select>
                    @error('payment_method') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Upload Nota Merah --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Foto Nota Merah / Bukti Kebutuhan <span class="text-red-500">*</span>
                </label>
                <div class="border-2 border-dashed @error('nota_photo') border-red-400 bg-red-50 @else border-slate-300 @enderror rounded-xl p-6 text-center hover:border-emerald-400 transition-colors relative">
                    <input type="file" id="nota_photo" name="nota_photo" accept=".jpg,.jpeg,.png,.pdf"
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                        onchange="previewFile(this, 'preview_nota')">
                    <div id="upload_nota_placeholder">
                        <i class="ph ph-file-arrow-up text-4xl text-slate-300 mb-2"></i>
                        <p class="text-sm text-slate-500 font-medium">Klik atau drag & drop file di sini</p>
                        <p class="text-xs text-slate-400 mt-1">JPG, PNG, PDF — Maks. 20MB</p>
                    </div>
                    <div id="preview_nota" class="hidden mt-2">
                        <img id="preview_nota_img" src="" alt="Preview" class="h-32 mx-auto rounded-lg object-cover border border-slate-200">
                        <p id="preview_nota_name" class="text-xs text-slate-500 mt-2"></p>
                    </div>
                </div>
                @error('nota_photo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Submit --}}
            <div class="flex gap-3 pt-2">
                <button type="submit"
                    class="flex-1 py-3 rounded-xl bg-emerald-500 text-white font-semibold text-sm hover:bg-emerald-600 hover:shadow-lg hover:shadow-emerald-500/20 transition-all flex items-center justify-center gap-2">
                    <i class="ph ph-paper-plane-tilt text-lg"></i>
                    Kirim Pengajuan
                </button>
                <a href="{{ route('nota-merah.index') }}"
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
        const placeholder = document.getElementById('upload_nota_placeholder');
        const nameEl = document.getElementById('preview_nota_name');
        const imgEl = document.getElementById('preview_nota_img');

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
