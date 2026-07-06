@extends('layouts.admin')

@section('title', 'Master Data Proyek - SanthiGraha')
@section('page_title', 'Master Data Proyek')

@section('content')
    <!-- Toolbar: Search, Filters -->
    <div
        class="bg-white rounded-t-2xl p-6 border-b border-slate-100 flex flex-col md:flex-row md:items-center justify-between gap-4">
        <form action="{{ route('projects.index') }}" method="GET" class="flex flex-col md:flex-row gap-3 w-full md:w-auto">
            <div class="relative">
                <i class="ph ph-magnifying-glass absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"></i>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama proyek, lokasi..."
                    class="pl-10 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none w-full md:w-64">
            </div>

            <button type="submit"
                class="px-5 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-xl transition-colors">
                Cari
            </button>
            @if(request()->filled('search'))
                <a href="{{ route('projects.index') }}"
                    class="px-5 py-2 bg-red-50 hover:bg-red-100 text-red-600 text-sm font-semibold rounded-xl transition-colors flex items-center justify-center">
                    Reset
                </a>
            @endif
        </form>

        <a href="{{ route('projects.create') }}"
            class="px-5 py-2.5  bg-emerald-500 hover:bg-emerald-600 hover:shadow-lg hover:shadow-emerald-500/20 transition-all text-white text-sm font-semibold rounded-xl shadow-lg shadow-brand-500/30 flex items-center gap-2 justify-center shrink-0">
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
                        <th class="px-6 py-4 text-center">STATUS</th>
                        <th class="px-6 py-4 text-center w-48">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($projects as $index => $project)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 text-center">
                                {{ $index + 1 + ($projects->currentPage() - 1) * $projects->perPage() }}
                            </td>
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
                            <td class="px-6 py-4 text-center">
                                @if($project->is_finished)
                                    {{-- Status: Selesai (ditandai admin) --}}
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-500">
                                        <i class="ph ph-check-circle"></i> Selesai
                                    </span>
                                @elseif($project->is_overdue)
                                    {{-- Status: Jatuh Tempo (end_date lewat, belum ditandai selesai) --}}
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-orange-50 text-orange-600 border border-orange-200">
                                        <i class="ph ph-warning-circle"></i> Jatuh Tempo
                                    </span>
                                @else
                                    {{-- Status: Aktif --}}
                                    <span
                                        class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-600">
                                        <i class="ph ph-circle-wavy-check"></i> Aktif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-1.5">

                                    @if($project->is_finished)
                                        {{-- Project selesai: hanya label terkunci --}}
                                        <span class="text-xs text-slate-400 italic">Terkunci</span>

                                    @elseif($project->is_overdue)
                                        {{-- Project jatuh tempo: Perpanjang + Selesaikan --}}
                                        <button type="button"
                                            onclick="openExtendModal({{ $project->id }}, '{{ addslashes($project->project_name) }}')"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-orange-50 text-orange-600 hover:bg-orange-500 hover:text-white transition-colors border border-orange-200 text-xs font-semibold"
                                            title="Perpanjang Deadline">
                                            <i class="ph ph-calendar-plus"></i> Perpanjang
                                        </button>
                                        <form action="{{ route('projects.complete', $project->id) }}" method="POST" class="inline"
                                            id="complete-form-{{ $project->id }}">
                                            @csrf
                                            <button type="button"
                                                onclick="confirmComplete('complete-form-{{ $project->id }}', '{{ addslashes($project->project_name) }}')"
                                                class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-emerald-50 text-emerald-600 hover:bg-emerald-500 hover:text-white transition-colors border border-emerald-200 text-xs font-semibold"
                                                title="Tandai Selesai">
                                                <i class="ph ph-check-fat"></i> Selesaikan
                                            </button>
                                        </form>

                                    @else
                                        {{-- Project aktif: Edit --}}
                                        <a href="{{ route('projects.edit', $project->id) }}"
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-blue-50 text-blue-600 hover:bg-blue-500 hover:text-white transition-colors border border-blue-200 text-xs font-semibold"
                                            title="Edit Proyek">
                                            <i class="ph ph-pencil"></i> Edit
                                        </a>
                                    @endif

                                    @if(!$project->is_finished)
                                        {{-- Tombol Hapus hanya ada jika belum selesai --}}
                                        <form action="{{ route('projects.destroy', $project->id) }}" method="POST"
                                            id="delete-form-{{ $project->id }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button"
                                                onclick="confirmDelete('delete-form-{{ $project->id }}', 'Apakah Anda yakin ingin menghapus proyek ini?')"
                                                class="w-8 h-8 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center hover:bg-slate-500 hover:text-white transition-colors border border-slate-200"
                                                title="Hapus Proyek">
                                                <i class="ph ph-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-400">
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

    <!-- ====================================================
                             Modal Perpanjang Deadline
                             ==================================================== -->
    <div id="extendModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 backdrop-blur-sm">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-md mx-4 overflow-hidden">
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-5 border-b border-slate-100">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-orange-50 flex items-center justify-center text-orange-500">
                        <i class="ph ph-calendar-plus text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-slate-800 text-base">Perpanjang Deadline</h3>
                        <p id="extendModalProjectName" class="text-xs text-slate-500 mt-0.5"></p>
                    </div>
                </div>
                <button type="button" onclick="closeExtendModal()"
                    class="w-8 h-8 rounded-lg text-slate-400 hover:text-slate-600 hover:bg-slate-100 flex items-center justify-center transition-colors">
                    <i class="ph ph-x"></i>
                </button>
            </div>

            <!-- Body -->
            <form id="extendForm" method="POST" action="">
                @csrf
                <div class="px-6 py-5 space-y-4">
                    <div class="p-3 rounded-xl bg-orange-50 border border-orange-100 flex items-start gap-2.5">
                        <i class="ph ph-warning-circle text-orange-500 text-lg mt-0.5 shrink-0"></i>
                        <p class="text-sm text-orange-700">
                            Project ini sudah melewati tenggat waktu. Tentukan tanggal deadline baru untuk melanjutkan
                            project.
                        </p>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">
                            Deadline Baru <span class="text-red-500">*</span>
                        </label>
                        <input type="date" name="new_end_date" id="extendDateInput"
                            min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                            class="w-full px-4 py-3 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-orange-400/20 focus:border-orange-400 outline-none transition-all text-slate-700"
                            required>
                    </div>
                </div>

                <!-- Footer -->
                <div class="flex items-center justify-end gap-3 px-6 py-4 border-t border-slate-100 bg-slate-50/50">
                    <button type="button" onclick="closeExtendModal()"
                        class="px-5 py-2.5 text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl text-sm font-semibold transition-colors">
                        Batal
                    </button>
                    <button type="submit"
                        class="px-5 py-2.5 bg-orange-500 hover:bg-orange-600 hover:shadow-lg hover:shadow-orange-500/20 transition-all text-white rounded-xl text-sm font-semibold shadow-md flex items-center gap-2">
                        <i class="ph ph-calendar-check text-base"></i>
                        Simpan Deadline Baru
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Buka modal perpanjang deadline
        function openExtendModal(projectId, projectName) {
            const modal = document.getElementById('extendModal');
            const form = document.getElementById('extendForm');
            const nameEl = document.getElementById('extendModalProjectName');

            form.action = `/projects/${projectId}/extend`;
            nameEl.textContent = projectName;
            document.getElementById('extendDateInput').value = '';

            modal.classList.remove('hidden');
            modal.classList.add('flex');
        }

        // Tutup modal perpanjang deadline
        function closeExtendModal() {
            const modal = document.getElementById('extendModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }

        // Tutup modal jika klik backdrop
        document.getElementById('extendModal').addEventListener('click', function (e) {
            if (e.target === this) closeExtendModal();
        });

        // Konfirmasi sebelum selesaikan project
        function confirmComplete(formId, projectName) {
            Swal.fire({
                title: 'Selesaikan Proyek?',
                html: `Tandai <strong>"${projectName}"</strong> sebagai selesai?<br>
                                           <span class="text-sm text-slate-500">Project tidak bisa diedit atau diperpanjang setelah ini.</span>`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Ya, Selesaikan!',
                cancelButtonText: 'Batal',
                customClass: {
                    popup: 'rounded-2xl shadow-xl border border-slate-100',
                    confirmButton: 'px-5 py-2.5 rounded-xl font-semibold text-sm mr-2',
                    cancelButton: 'px-5 py-2.5 rounded-xl font-semibold text-sm'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(formId).submit();
                }
            });
        }
    </script>
@endsection