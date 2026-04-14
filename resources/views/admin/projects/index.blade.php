@extends('layouts.admin')

@section('title', 'Master Data Proyek - SanthiGraha')
@section('page_title', 'Master Data Proyek')

@section('content')
    <!-- Toolbar: Search, Filters -->
    <div class="bg-white rounded-t-2xl p-6 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form action="{{ route('projects.index') }}" method="GET" class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
            <div class="relative">
                <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama proyek, lokasi..." class="pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none w-full md:w-64">
            </div>
            
            <button type="submit" class="px-5 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-xl transition-colors">
                Cari
            </button>
            @if(request()->filled('search'))
                <a href="{{ route('projects.index') }}" class="px-5 py-2 bg-red-50 hover:bg-red-100 text-red-600 text-sm font-semibold rounded-xl transition-colors flex items-center justify-center">
                    Reset
                </a>
            @endif
        </form>

        <a href="{{ route('projects.create') }}" class="px-5 py-2.5 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl shadow-lg shadow-brand-500/30 transition-all flex items-center gap-2 justify-center shrink-0">
            <i class="ph ph-plus-circle text-lg"></i>
            Tambah Proyek
        </a>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-b-2xl shadow-sm border border-t-0 border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-4 w-12 text-center">NO</th>
                        <th class="px-6 py-4">NAMA PROYEK</th>
                        <th class="px-6 py-4">LOKASI</th>
                        <th class="px-6 py-4">TANGGAL MULAI</th>
                        <th class="px-6 py-4">TANGGAL SELESAI</th>
                        <th class="px-6 py-4 text-center w-32">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($projects as $index => $project)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 text-center">{{ $index + 1 + ($projects->currentPage() - 1) * $projects->perPage() }}</td>
                        <td class="px-6 py-4 font-medium text-slate-800">{{ $project->project_name }}</td>
                        <td class="px-6 py-4">{{ $project->location ?: '-' }}</td>
                        <td class="px-6 py-4">
                            @if($project->start_date)
                                {{ \Carbon\Carbon::parse($project->start_date)->format('d M Y') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($project->end_date)
                                {{ \Carbon\Carbon::parse($project->end_date)->format('d M Y') }}
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('projects.edit', $project->id) }}" class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center hover:bg-blue-500 hover:text-white transition-colors border border-blue-200" title="Edit Proyek">
                                    <i class="ph ph-pencil-simple"></i>
                                </a>
                                <form action="{{ route('projects.destroy', $project->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus proyek ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center hover:bg-slate-500 hover:text-white transition-colors border border-slate-200" title="Hapus Proyek">
                                        <i class="ph ph-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center text-slate-400">
                            <i class="ph ph-building text-5xl mb-4 text-slate-200"></i>
                            <p class="text-base font-medium text-slate-500">Belum ada proyek</p>
                            <p class="text-sm">Silakan tambah data proyek baru.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination Links -->
        @if($projects->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 text-sm">
            {{ $projects->links() }}
        </div>
        @endif
    </div>
@endsection
