@extends('layouts.pegawai')

@section('title', 'Nota Merah - SanthiGraha')
@section('page_title', 'Nota Merah')

@section('content')
    <div class="mb-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Pengajuan Dana (Nota Merah)</h2>
            <p class="text-sm text-slate-500 mt-1">Ajukan kebutuhan belanja sebelum transaksi terjadi.</p>
        </div>
        <a href="{{ route('nota-merah.create') }}"
            class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-500 text-white font-semibold text-sm hover:bg-emerald-600 hover:shadow-lg hover:shadow-emerald-500/20 transition-all">
            <i class="ph ph-plus text-lg"></i> Ajukan Nota Merah
        </a>
    </div>

    {{-- Filter Status --}}
    <form method="GET" action="{{ route('nota-merah.index') }}" class="mb-5 flex flex-wrap gap-3">
        <div class="relative flex-1 min-w-[180px]">
            <i
                class="ph ph-magnifying-glass absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-lg pointer-events-none"></i>
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Cari proyek, kategori, deskripsi..."
                class="w-full pl-10 pr-4 py-2.5 rounded-xl border border-slate-200 text-sm focus:ring-2 focus:ring-emerald-400 focus:border-emerald-400 outline-none bg-white">
        </div>
        <select name="status"
            class="px-4 py-2.5 rounded-xl border border-slate-200 text-sm bg-white focus:ring-2 focus:ring-emerald-400 outline-none">
            <option value="">Semua Status</option>
            <option value="menunggu_persetujuan" @selected(request('status') === 'menunggu_persetujuan')>Menunggu Persetujuan
            </option>
            <option value="disetujui" @selected(request('status') === 'disetujui')>Disetujui</option>
            <option value="ditolak" @selected(request('status') === 'ditolak')>Ditolak</option>
            <option value="menunggu_konfirmasi" @selected(request('status') === 'menunggu_konfirmasi')>Menunggu Konfirmasi
            </option>
            <option value="selesai" @selected(request('status') === 'selesai')>Selesai</option>
        </select>
        <button type="submit"
            class="px-5 py-2.5 rounded-xl bg-slate-700 text-white text-sm font-medium hover:bg-slate-800 transition-colors">Cari</button>
        @if(request()->anyFilled(['search', 'status']))
            <a href="{{ route('nota-merah.index') }}"
                class="px-5 py-2.5 rounded-xl bg-slate-100 text-slate-600 text-sm font-medium hover:bg-slate-200 transition-colors">Reset</a>
        @endif
    </form>

    {{-- Tabel --}}
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="px-5 py-4">TANGGAL PENGAJUAN</th>
                        <th class="px-5 py-4">PROYEK & KATEGORI</th>
                        <th class="px-5 py-4">NOMINAL</th>
                        <th class="px-5 py-4 text-center">STATUS</th>
                        <th class="px-5 py-4 text-center">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($notaMerahs as $nota)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="font-medium text-slate-800">{{ $nota->created_at->format('d M Y') }}</div>
                                <div class="text-xs text-slate-400 mt-0.5">{{ $nota->created_at->format('H:i') }} WIB</div>
                            </td>
                            <td class="px-5 py-4">
                                <div class="font-medium text-slate-800">{{ $nota->project->project_name ?? '-' }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">{{ $nota->category->category_name ?? '-' }}</div>
                                @if($nota->description)
                                    <div class="text-[12px] text-slate-400 mt-1 italic">{{ Str::limit($nota->description, 50) }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="font-bold text-red-600">Rp {{ number_format($nota->amount, 2, ',', '.') }}</span>
                                <div class="text-[11px] text-slate-400 mt-0.5 uppercase font-medium tracking-wide">
                                    {{ $nota->payment_method }}</div>
                            </td>
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold {{ $nota->status_color }}">
                                    @if($nota->status === 'menunggu_persetujuan')
                                        <i class="ph ph-clock"></i>
                                    @elseif($nota->status === 'disetujui')
                                        <i class="ph ph-check-circle"></i>
                                    @elseif($nota->status === 'ditolak')
                                        <i class="ph ph-x-circle"></i>
                                    @elseif($nota->status === 'menunggu_konfirmasi')
                                        <i class="ph ph-hourglass"></i>
                                    @elseif($nota->status === 'selesai')
                                        <i class="ph ph-check-square"></i>
                                    @endif
                                    {{ $nota->status_label }}
                                </span>

                                {{-- Tombol Upload Realisasi jika sudah disetujui --}}
                                @if($nota->status === 'disetujui')
                                    <div class="mt-2">
                                        <a href="{{ route('nota-merah.realisasi.form', $nota->id) }}"
                                            class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg bg-purple-500 text-white text-xs font-semibold hover:bg-purple-600 transition-colors">
                                            <i class="ph ph-upload-simple"></i> Upload Realisasi
                                        </a>
                                    </div>
                                @endif

                                {{-- Info penolakan --}}
                                @if($nota->status === 'ditolak' && $nota->rejection_reason)
                                    <div class="mt-1.5 text-[11px] text-red-500 italic text-left px-1">
                                        <i class="ph ph-warning"></i> {{ Str::limit($nota->rejection_reason, 60) }}
                                    </div>
                                @endif
                            </td>
                            <td class="px-5 py-4 whitespace-nowrap text-center">
                                <div class="flex items-center justify-center gap-2">
                                    <a href="{{ route('nota-merah.show', $nota->id) }}"
                                        class="p-2 rounded-lg bg-slate-100 text-slate-600 hover:bg-slate-200 transition-colors"
                                        title="Lihat Detail">
                                        <i class="ph ph-eye text-base"></i>
                                    </a>
                                    @if($nota->status === 'ditolak')
                                        <a href="{{ route('nota-merah.edit', $nota->id) }}"
                                            class="p-2 rounded-lg bg-amber-50 text-amber-600 hover:bg-amber-500 hover:text-white transition-colors"
                                            title="Edit & Kirim Ulang">
                                            <i class="ph ph-pencil-simple text-base"></i>
                                        </a>
                                    @endif
                                    @if(in_array($nota->status, ['menunggu_persetujuan', 'ditolak']))
                                        <form action="{{ route('nota-merah.destroy', $nota->id) }}" method="POST"
                                            id="delete-form-{{ $nota->id }}" class="inline">
                                            @csrf @method('DELETE')
                                            <button type="button"
                                                onclick="confirmDelete('delete-form-{{ $nota->id }}', 'Apakah Anda yakin ingin menghapus nota merah ini?')"
                                                class="p-2 rounded-lg bg-red-50 text-red-500 hover:bg-red-500 hover:text-white transition-colors"
                                                title="Hapus">
                                                <i class="ph ph-trash text-base"></i>
                                            </button>
                                        </form>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-slate-50 text-slate-400 text-xs font-semibold border border-slate-100 cursor-not-allowed"
                                            title="Terkunci (Nota merah sudah valid/diproses)">
                                            <i class="ph ph-lock text-sm"></i> Terkunci
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="w-16 h-16 bg-red-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="ph ph-file-text text-3xl text-red-300"></i>
                                </div>
                                <p class="text-base font-bold text-slate-800">Belum Ada Nota Merah</p>
                                <p class="text-sm text-slate-500 mt-1">Klik tombol "Ajukan Nota Merah" untuk memulai pengajuan
                                    dana.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($notaMerahs->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 text-sm">
                {{ $notaMerahs->links() }}
            </div>
        @endif
    </div>

    {{-- Legend --}}
    <div class="mt-5 flex flex-wrap gap-3 text-xs text-slate-500">
        <span class="font-semibold text-slate-600">Alur:</span>
        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-amber-400 inline-block"></span>
            Ajukan</span>
        <i class="ph ph-arrow-right text-slate-300"></i>
        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-blue-400 inline-block"></span> Disetujui
            Admin</span>
        <i class="ph ph-arrow-right text-slate-300"></i>
        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-purple-400 inline-block"></span> Upload
            Realisasi</span>
        <i class="ph ph-arrow-right text-slate-300"></i>
        <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-400 inline-block"></span>
            Dikonfirmasi → Kas</span>
    </div>
@endsection