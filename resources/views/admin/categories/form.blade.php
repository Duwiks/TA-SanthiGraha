@extends('layouts.admin')

@section('title', (isset($category) ? 'Edit Kategori' : 'Tambah Kategori') . ' - SanthiGraha')
@section('page_title', isset($category) ? 'Edit Kategori' : 'Tambah Kategori Baru')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-6 md:p-8">
            <form action="{{ isset($category) ? route('categories.update', $category->id) : route('categories.store') }}" method="POST">
                @csrf
                @if(isset($category))
                    @method('PUT')
                @endif
                
                @if($errors->any())
                <div class="mb-6 p-4 rounded-xl bg-red-50 text-red-600 border border-red-100 px-4 py-3 text-sm">
                    <ul class="list-disc pl-5">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                <div class="space-y-5">
                    <!-- Nama Kategori -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Kategori <span class="text-red-500">*</span></label>
                        <input type="text" name="category_name" value="{{ old('category_name', $category->category_name ?? '') }}" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all" placeholder="Contoh: Pembelian Material" required>
                    </div>

                    <!-- Deskripsi -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Deskripsi</label>
                        <textarea name="description" rows="4" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all resize-none" placeholder="Deskripsi Kategori (opsional)">{{ old('description', $category->description ?? '') }}</textarea>
                    </div>
                </div>

                <div class="mt-8 flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                    <a href="{{ route('categories.index') }}" class="px-5 py-2.5 text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl text-sm font-semibold transition-colors">
                        Batal
                    </a>
                    <button type="submit" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-semibold shadow-lg shadow-brand-500/30 transition-all flex items-center gap-2">
                        <i class="ph ph-check-circle text-lg"></i>
                        Simpan Kategori
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
