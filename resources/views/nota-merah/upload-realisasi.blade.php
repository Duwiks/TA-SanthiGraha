@extends('layouts.pegawai')

@section('title', 'Upload Bukti Realisasi - SanthiGraha')
@section('page_title', 'Upload Bukti Realisasi')

@section('content')
<div class="max-w-2xl mx-auto">

    <div class="mb-6">
        <a href="{{ route('nota-merah.show', $nota->id) }}" class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-emerald-600 transition-colors mb-4">
            <i class="ph ph-arrow-left"></i> Kembali ke Detail
        </a>
        <h2 class="text-lg font-bold text-slate-800">Upload Bukti Realisasi</h2>
        <p class="text-sm text-slate-500 mt-1">Nota Merah #{{ $nota->id }} — telah disetujui. Lampirkan struk / kwitansi pembelian.</p>
    </div>

    {{-- Ringkasan Pengajuan --}}
    <div class="bg-blue-50 border border-blue-200 rounded-xl p-5 mb-6">
        <h3 class="text-sm font-bold text-blue-800 mb-3 flex items-center gap-2">
            <i class="ph ph-check-circle text-blue-500"></i> Nota Merah yang Disetujui
        </h3>
        <div class="grid grid-cols-2 gap-x-6 gap-y-2 text-sm">
            <div>
                <span class="text-blue-600">Proyek:</span>
                <span class="font-semibold text-blue-900 ml-1">{{ $nota->project->project_name ?? '-' }}</span>
            </div>
            <div>
                <span class="text-blue-600">Kategori:</span>
                <span class="font-semibold text-blue-900 ml-1">{{ $nota->category->category_name ?? '-' }}</span>
            </div>
            <div>
                <span class="text-blue-600">Nominal Disetujui:</span>
                <span class="font-bold text-red-600 ml-1">Rp {{ number_format($nota->amount, 2, ',', '.') }}</span>
            </div>
            <div>
                <span class="text-blue-600">Metode:</span>
                <span class="font-semibold text-blue-900 ml-1">{{ $nota->bank_tujuan }}</span>
            </div>
        </div>
    </div>

    {{-- Form Upload --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
        <form action="{{ route('nota-merah.realisasi.store', $nota->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            {{-- Tanggal Realisasi --}}
            <div>
                <label for="realisasi_date" class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Tanggal Belanja Realisasi <span class="text-red-500">*</span>
                </label>
                <input type="date" id="realisasi_date"
                    value="{{ $nota->nota_date->format('Y-m-d') }}"
                    disabled
                    class="w-full px-4 py-3 rounded-xl border border-slate-200 bg-slate-50 text-slate-500 text-sm cursor-not-allowed outline-none">
                <input type="hidden" name="realisasi_date" value="{{ $nota->nota_date->format('Y-m-d') }}">
                @error('realisasi_date') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                <p class="text-xs text-slate-400 mt-1 flex items-center gap-1">
                    <i class="ph ph-lock-simple"></i>
                    Tanggal otomatis diisi sesuai tanggal nota merah dan tidak dapat diubah.
                </p>
            </div>

            {{-- Upload Bukti Realisasi --}}
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Foto Struk / Kwitansi Pembelian <span class="text-red-500">*</span>
                </label>
                <div class="border-2 border-dashed @error('realisasi_photo') border-red-400 bg-red-50 @else border-slate-300 @enderror rounded-xl p-6 text-center hover:border-purple-400 transition-colors relative">
                    <input type="file" id="realisasi_photo" name="realisasi_photo" accept=".jpg,.jpeg,.png,.pdf"
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                        onchange="previewRealisasi(this)">
                    <div id="upload_placeholder">
                        <i class="ph ph-receipt text-4xl text-slate-300 mb-2"></i>
                        <p class="text-sm text-slate-500 font-medium">Klik atau drag & drop file di sini</p>
                        <p class="text-xs text-slate-400 mt-1">JPG, PNG, PDF — Otomatis dikompres jika >5MB</p>
                    </div>
                    <div id="preview_area" class="hidden mt-2">
                        <img id="preview_img" src="" alt="Preview" class="h-40 mx-auto rounded-lg object-cover border border-slate-200">
                        <p id="preview_name" class="text-xs text-slate-500 mt-2"></p>
                    </div>
                </div>
                @error('realisasi_photo') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                <p class="text-xs text-slate-400 mt-1">Upload foto struk, kwitansi, atau invoice pembelian sebagai bukti.</p>
            </div>

            {{-- Peringatan --}}
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex gap-3">
                <i class="ph ph-warning text-amber-500 text-xl flex-shrink-0 mt-0.5"></i>
                <div class="text-xs text-amber-700">
                    <p class="font-semibold mb-1">Perhatian sebelum upload:</p>
                    <ul class="list-disc ml-3 space-y-0.5">
                        <li>Pastikan bukti realisasi sesuai dengan nominal yang disetujui</li>
                        <li>Foto harus jelas dan mudah dibaca oleh Admin</li>
                        <li>Setelah diupload, Admin akan mengkonfirmasi dan mencatat ke kas</li>
                    </ul>
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex gap-3 pt-2">
                <button type="submit"
                    class="flex-1 py-3 rounded-xl bg-purple-500 text-white font-semibold text-sm hover:bg-purple-600 hover:shadow-lg hover:shadow-purple-500/20 transition-all flex items-center justify-center gap-2">
                    <i class="ph ph-upload-simple text-lg"></i>
                    Kirim Bukti Realisasi
                </button>
                <a href="{{ route('nota-merah.show', $nota->id) }}"
                    class="px-6 py-3 rounded-xl bg-slate-100 text-slate-600 font-semibold text-sm hover:bg-slate-200 transition-colors text-center">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    function previewRealisasi(input) {
        const file = input.files[0];
        if (!file) return;
        const preview = document.getElementById('preview_area');
        const placeholder = document.getElementById('upload_placeholder');
        const nameEl = document.getElementById('preview_name');
        const imgEl = document.getElementById('preview_img');

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
