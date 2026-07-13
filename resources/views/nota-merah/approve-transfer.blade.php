@extends('layouts.admin')

@section('title', 'Setujui & Upload Bukti Transfer - SanthiGraha')
@section('page_title', 'Upload Bukti Transfer')

@section('content')
<div class="max-w-2xl mx-auto">

    {{-- Header --}}
    <div class="mb-6">
        <a href="{{ route('nota-merah.show', $nota->id) }}"
            class="inline-flex items-center gap-1.5 text-sm text-slate-500 hover:text-emerald-600 transition-colors mb-4">
            <i class="ph ph-arrow-left"></i> Kembali ke Detail
        </a>
        <h2 class="text-lg font-bold text-slate-800">Setujui & Upload Bukti Transfer</h2>
        <p class="text-sm text-slate-500 mt-1">Upload bukti transfer untuk menyetujui pengajuan dana Nota Merah #{{ $nota->id }}.</p>
    </div>

    {{-- Info Ringkasan Nota Merah --}}
    <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 mb-6">
        <h3 class="text-sm font-bold text-slate-700 mb-4 flex items-center gap-2">
            <i class="ph ph-file-text text-emerald-500"></i> Ringkasan Pengajuan
        </h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-8 gap-y-3 text-sm">
            <div class="flex justify-between sm:flex-col sm:justify-start gap-1">
                <span class="text-slate-400 text-xs font-medium uppercase tracking-wide">Pegawai</span>
                <span class="font-semibold text-slate-800">{{ $nota->user->name ?? '-' }}</span>
            </div>
            <div class="flex justify-between sm:flex-col sm:justify-start gap-1">
                <span class="text-slate-400 text-xs font-medium uppercase tracking-wide">Proyek</span>
                <span class="font-semibold text-slate-800">{{ $nota->project->project_name ?? '-' }}</span>
            </div>
            <div class="flex justify-between sm:flex-col sm:justify-start gap-1">
                <span class="text-slate-400 text-xs font-medium uppercase tracking-wide">Nominal</span>
                <span class="font-bold text-red-600 text-base">Rp {{ number_format($nota->amount, 2, ',', '.') }}</span>
            </div>
            <div class="flex justify-between sm:flex-col sm:justify-start gap-1">
                <span class="text-slate-400 text-xs font-medium uppercase tracking-wide">Kategori</span>
                <span class="font-semibold text-slate-800">{{ $nota->category->category_name ?? '-' }}</span>
            </div>
        </div>

        {{-- Info Rekening Tujuan --}}
        @if($nota->bank_tujuan || $nota->no_rekening || $nota->nama_pemilik_rekening)
        <div class="mt-4 pt-4 border-t border-slate-100">
            <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide mb-3 flex items-center gap-1">
                <i class="ph ph-bank text-emerald-400"></i> Rekening Tujuan Transfer
            </p>
            <div class="bg-emerald-50 rounded-xl p-4 flex flex-col gap-2 border border-emerald-100">
                @if($nota->bank_tujuan)
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-500">Bank</span>
                    <span class="font-bold text-slate-800">{{ $nota->bank_tujuan }}</span>
                </div>
                @endif
                @if($nota->no_rekening)
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-500">No. Rekening</span>
                    <span class="font-bold text-slate-800 font-mono tracking-wider text-base">{{ $nota->no_rekening }}</span>
                </div>
                @endif
                @if($nota->nama_pemilik_rekening)
                <div class="flex items-center justify-between text-sm">
                    <span class="text-slate-500">A.n.</span>
                    <span class="font-bold text-slate-800">{{ $nota->nama_pemilik_rekening }}</span>
                </div>
                @endif
            </div>
        </div>
        @endif
    </div>

    {{-- Form Upload --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
        <form action="{{ route('nota-merah.approve.store', $nota->id) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                    Bukti Transfer <span class="text-red-500">*</span>
                </label>
                <p class="text-xs text-slate-400 mb-3">Upload screenshot atau foto bukti transfer yang sudah dilakukan ke rekening pegawai.</p>

                <div id="upload-wrapper"
                    class="border-2 border-dashed @error('transfer_proof') border-red-400 bg-red-50 @else border-slate-300 @enderror rounded-xl p-8 text-center hover:border-emerald-400 transition-colors relative cursor-pointer">
                    <input type="file" id="transfer_proof" name="transfer_proof" accept=".jpg,.jpeg,.png,.pdf"
                        class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
                        onchange="previewTransfer(this)">
                    <div id="upload-placeholder">
                        <i class="ph ph-file-arrow-up text-5xl text-slate-300 mb-3"></i>
                        <p class="text-sm text-slate-600 font-semibold">Klik atau drag & drop file di sini</p>
                        <p class="text-xs text-slate-400 mt-1">JPG, PNG, PDF — Otomatis dikompres jika >5MB</p>
                    </div>
                    <div id="upload-preview" class="hidden">
                        <img id="preview-img" src="" alt="Preview" class="h-40 mx-auto rounded-xl object-cover border border-slate-200 shadow-sm">
                        <p id="preview-name" class="text-xs text-slate-500 mt-3 font-medium"></p>
                        <button type="button" onclick="clearPreview()"
                            class="mt-2 text-xs text-red-400 hover:text-red-600 underline transition-colors">
                            Ganti file
                        </button>
                    </div>
                </div>
                @error('transfer_proof') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Warning --}}
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex gap-3">
                <i class="ph ph-warning text-amber-500 text-lg flex-shrink-0 mt-0.5"></i>
                <div class="text-xs text-amber-700 leading-relaxed">
                    <p class="font-semibold mb-0.5">Perhatian:</p>
                    <p>Setelah Anda klik <strong>"Setujui & Upload Bukti Transfer"</strong>, status nota merah akan berubah menjadi <strong>Menunggu Realisasi</strong> dan pegawai akan dapat mengupload bukti realisasi pembelian. Tindakan ini tidak dapat dibatalkan.</p>
                </div>
            </div>

            {{-- Submit --}}
            <div class="flex gap-3 pt-2">
                <button type="submit"
                    class="flex-1 py-3.5 rounded-xl bg-emerald-500 text-white font-semibold text-sm hover:bg-emerald-600 hover:shadow-lg hover:shadow-emerald-500/20 transition-all flex items-center justify-center gap-2">
                    <i class="ph ph-check-circle text-lg"></i>
                    Setujui & Upload Bukti Transfer
                </button>
                <a href="{{ route('nota-merah.show', $nota->id) }}"
                    class="px-6 py-3.5 rounded-xl bg-slate-100 text-slate-600 font-semibold text-sm hover:bg-slate-200 transition-colors text-center flex items-center">
                    Batal
                </a>
            </div>
        </form>
    </div>
</div>

<script>
    function previewTransfer(input) {
        const file = input.files[0];
        if (!file) return;

        const placeholder = document.getElementById('upload-placeholder');
        const previewDiv  = document.getElementById('upload-preview');
        const previewImg  = document.getElementById('preview-img');
        const previewName = document.getElementById('preview-name');

        placeholder.classList.add('hidden');
        previewDiv.classList.remove('hidden');

        if (file.type === 'application/pdf') {
            previewImg.src = '';
            previewImg.classList.add('hidden');
            previewName.textContent = '📄 ' + file.name;
        } else {
            const reader = new FileReader();
            reader.onload = (e) => {
                previewImg.classList.remove('hidden');
                previewImg.src = e.target.result;
                previewName.textContent = file.name;
            };
            reader.readAsDataURL(file);
        }
    }

    function clearPreview() {
        document.getElementById('transfer_proof').value = '';
        document.getElementById('upload-placeholder').classList.remove('hidden');
        document.getElementById('upload-preview').classList.add('hidden');
        document.getElementById('preview-img').src = '';
        document.getElementById('preview-name').textContent = '';
        document.getElementById('preview-img').classList.remove('hidden');
    }
</script>
@endsection
