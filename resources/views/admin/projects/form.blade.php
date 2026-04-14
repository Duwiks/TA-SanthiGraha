@extends('layouts.admin')

@section('title', (isset($project) ? 'Edit Proyek' : 'Tambah Proyek') . ' - SanthiGraha')
@section('page_title', isset($project) ? 'Edit Proyek' : 'Tambah Proyek Baru')

@section('content')
<div class="max-w-3xl mx-auto">
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-6 md:p-8">
            <form action="{{ isset($project) ? route('projects.update', $project->id) : route('projects.store') }}" method="POST">
                @csrf
                @if(isset($project))
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

                <div class="space-y-6">
                    <!-- Nama Proyek -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nama Proyek <span class="text-red-500">*</span></label>
                        <input type="text" name="project_name" value="{{ old('project_name', $project->project_name ?? '') }}" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all" placeholder="Contoh: Pembangunan Perumahan Tahap 1" required>
                    </div>

                    <!-- Lokasi -->
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Lokasi Proyek</label>
                        <input type="text" name="location" value="{{ old('location', $project->location ?? '') }}" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all" placeholder="Contoh: Jl. Sudirman No 1">
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Tanggal Mulai -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tanggal Mulai</label>
                            <input type="date" name="start_date" value="{{ old('start_date', isset($project->start_date) ? \Carbon\Carbon::parse($project->start_date)->format('Y-m-d') : '') }}" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all text-slate-700">
                        </div>

                        <!-- Tanggal Selesai -->
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tanggal Selesai</label>
                            <input type="date" name="end_date" value="{{ old('end_date', isset($project->end_date) ? \Carbon\Carbon::parse($project->end_date)->format('Y-m-d') : '') }}" class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none transition-all text-slate-700">
                        </div>
                    </div>
                </div>

                <div class="mt-8 flex items-center justify-end gap-3 pt-6 border-t border-slate-100">
                    <a href="{{ route('projects.index') }}" class="px-5 py-2.5 text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl text-sm font-semibold transition-colors">
                        Batal
                    </a>
                    <button type="submit" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white rounded-xl text-sm font-semibold shadow-lg shadow-brand-500/30 transition-all flex items-center gap-2">
                        <i class="ph ph-check-circle text-lg"></i>
                        Simpan Proyek
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
