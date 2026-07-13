@extends('layouts.admin')

@section('title', 'Approval Transaksi - SanthiGraha')
@section('page_title', 'Approval Transaksi')

@section('content')

    {{-- ================================================================
    BAGIAN 1: ANTREAN PENDING
    ================================================================ --}}
    <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Antrean Menunggu Persetujuan</h2>
            <p class="text-sm text-slate-500 mt-1">Transaksi yang diajukan pegawai dan butuh tindakan Anda.</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            @if($transactions->total() > 0)
                <div
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-amber-50 border border-amber-200 text-amber-700 text-sm font-bold">
                    <i class="ph ph-clock text-base"></i> {{ $transactions->total() }} menunggu
                </div>
            @endif
            {{-- Sort Antrean --}}
            <a href="{{ route('approvals.index', array_merge(request()->query(), ['sort' => 'latest'])) }}"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all
                      {{ request('sort', 'latest') === 'latest' ? 'bg-slate-700 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                <i class="ph ph-sort-descending"></i> Terbaru
            </a>
            <a href="{{ route('approvals.index', array_merge(request()->query(), ['sort' => 'oldest'])) }}"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all
                      {{ request('sort') === 'oldest' ? 'bg-slate-700 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                <i class="ph ph-sort-ascending"></i> Terlama
            </a>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden mb-10">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="px-5 py-4">TANGGAL PENGAJUAN</th>
                        <th class="px-5 py-4">TANGGAL NOTA</th>
                        <th class="px-5 py-4">PROYEK & KATEGORI</th>
                        <th class="px-5 py-4">NOMINAL</th>
                        <th class="px-5 py-4">PENGAJU</th>
                        <th class="px-5 py-4 text-center">TINDAKAN</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $trx)
                        <tr class="hover:bg-slate-50 transition-colors">

                            {{-- Tanggal Pengajuan --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="font-medium text-slate-800">
                                    {{ \Carbon\Carbon::parse($trx->created_at)->format('d M Y') }}</div>
                                <div class="text-xs text-slate-400 mt-0.5">
                                    {{ \Carbon\Carbon::parse($trx->created_at)->format('H:i') }} WITA</div>
                            </td>

                            {{-- Tanggal Nota --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="font-medium text-slate-700">
                                    {{ \Carbon\Carbon::parse($trx->transaction_date)->format('d M Y') }}
                                </div>
                            </td>

                            {{-- Proyek & Kategori --}}
                            <td class="px-5 py-4">
                                <div class="font-medium text-slate-800">{{ $trx->project->project_name ?? '-' }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">{{ $trx->category->category_name ?? '-' }}</div>
                                @if($trx->description)
                                    <div class="text-[12px] text-slate-400 mt-1 italic">{{ Str::limit($trx->description, 50) }}
                                    </div>
                                @endif
                                @if($trx->receipt_photo)
                                    <div class="mt-2">
                                        @if(str_ends_with(strtolower($trx->receipt_photo), '.pdf'))
                                            <a href="{{ asset('storage/' . $trx->receipt_photo) }}" target="_blank"
                                                class="inline-flex items-center gap-1 text-[11px] font-semibold text-red-600 bg-red-50 px-2.5 py-1 rounded-lg hover:bg-red-100 transition-colors border border-red-100">
                                                <i class="ph ph-file-pdf"></i> Lihat PDF
                                            </a>
                                        @else
                                            <a href="{{ asset('storage/' . $trx->receipt_photo) }}" target="_blank">
                                                <img src="{{ asset('storage/' . $trx->receipt_photo) }}" alt="Bukti"
                                                    class="h-14 w-20 object-cover rounded-lg border border-slate-200 hover:opacity-80 transition-opacity">
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </td>

                            {{-- Nominal --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <span class="font-bold {{ $trx->type === 'pemasukan' ? 'text-emerald-600' : 'text-red-600' }}">
                                    {{ $trx->type === 'pemasukan' ? '+' : '-' }} Rp
                                    {{ number_format($trx->amount, 2, ',', '.') }}
                                </span>
                                <div class="flex items-center gap-1.5 mt-0.5">
                                    <span
                                        class="text-[10px] uppercase font-bold tracking-wide px-2 py-0.5 rounded
                                        {{ $trx->type === 'pemasukan' ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600' }}">
                                        {{ $trx->type }}
                                    </span>
                                    @if($trx->payment_method)
                                        <span
                                            class="text-[10px] uppercase font-medium tracking-wide text-slate-400">{{ $trx->payment_method }}</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Pengaju --}}
                            <td class="px-5 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div
                                        class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs shrink-0">
                                        {{ substr($trx->user->name ?? 'U', 0, 1) }}
                                    </div>
                                    <span class="font-medium text-slate-700 text-sm">{{ $trx->user->name ?? '-' }}</span>
                                </div>
                            </td>

                            {{-- Tindakan --}}
                            <td class="px-5 py-4 text-center whitespace-nowrap">
                                <div class="flex items-center justify-center gap-2">
                                    <form action="{{ route('transactions.approve', $trx->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit"
                                            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-emerald-500 text-white font-semibold text-xs hover:bg-emerald-600 hover:shadow-lg hover:shadow-emerald-500/20 transition-all">
                                            <i class="ph ph-check-circle text-sm"></i> Setujui
                                        </button>
                                    </form>
                                    <button onclick="rejectTransaction({{ $trx->id }})"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl bg-red-50 text-red-600 font-semibold text-xs hover:bg-red-500 hover:text-white transition-all border border-red-100 hover:border-red-500">
                                        <i class="ph ph-x-circle text-sm"></i> Tolak
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center">
                                <div class="w-16 h-16 bg-emerald-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <i class="ph ph-check-square text-3xl text-emerald-300"></i>
                                </div>
                                <p class="text-base font-bold text-slate-800">Antrean Bersih</p>
                                <p class="text-sm text-slate-500 mt-1">Tidak ada transaksi yang menunggu persetujuan saat ini.
                                </p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($transactions->hasPages())
            <div class="px-6 py-4 border-t border-slate-100 text-sm">
                {{ $transactions->links() }}
            </div>
        @endif
    </div>


    {{-- ================================================================
    BAGIAN 2: RIWAYAT PERSETUJUAN
    ================================================================ --}}
    <div class="mb-5 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h2 class="text-lg font-bold text-slate-800">Riwayat Persetujuan</h2>
            <p class="text-sm text-slate-500 mt-1">Log semua transaksi yang sudah diproses.</p>
        </div>

        <div class="flex items-center gap-2 flex-wrap">
            {{-- Sort Riwayat --}}
            <a href="{{ route('approvals.index', array_merge(request()->query(), ['sort' => 'latest'])) }}"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all
                      {{ request('sort', 'latest') === 'latest' ? 'bg-slate-700 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                <i class="ph ph-sort-descending"></i> Terbaru
            </a>
            <a href="{{ route('approvals.index', array_merge(request()->query(), ['sort' => 'oldest'])) }}"
                class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold transition-all
                      {{ request('sort') === 'oldest' ? 'bg-slate-700 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                <i class="ph ph-sort-ascending"></i> Terlama
            </a>

            {{-- Filter Tab --}}
            <a href="{{ route('approvals.index', ['sort' => request('sort', 'latest')]) }}"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold transition-all
                      {{ !request('filter_status') ? 'bg-slate-700 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                <i class="ph ph-list-bullets"></i> Semua
                <span
                    class="ml-1 text-[11px] font-bold px-1.5 py-0.5 rounded-full {{ !request('filter_status') ? 'bg-white/20 text-white' : 'bg-slate-100 text-slate-500' }}">
                    {{ $historyApprovedCount + $historyRejectedCount }}
                </span>
            </a>
            <a href="{{ route('approvals.index', ['filter_status' => 'approved', 'sort' => request('sort', 'latest')]) }}"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold transition-all
                      {{ request('filter_status') === 'approved' ? 'bg-emerald-500 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                <i class="ph ph-check-circle"></i> Disetujui
                <span
                    class="ml-1 text-[11px] font-bold px-1.5 py-0.5 rounded-full {{ request('filter_status') === 'approved' ? 'bg-white/20 text-white' : 'bg-emerald-50 text-emerald-600' }}">
                    {{ $historyApprovedCount }}
                </span>
            </a>
            <a href="{{ route('approvals.index', ['filter_status' => 'rejected', 'sort' => request('sort', 'latest')]) }}"
                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-sm font-semibold transition-all
                      {{ request('filter_status') === 'rejected' ? 'bg-red-500 text-white' : 'bg-white border border-slate-200 text-slate-600 hover:bg-slate-50' }}">
                <i class="ph ph-x-circle"></i> Ditolak
                <span
                    class="ml-1 text-[11px] font-bold px-1.5 py-0.5 rounded-full {{ request('filter_status') === 'rejected' ? 'bg-white/20 text-white' : 'bg-red-50 text-red-600' }}">
                    {{ $historyRejectedCount }}
                </span>
            </a>
        </div>
    </div>

    {{-- Riwayat sebagai Tabel (konsisten dengan halaman lain) --}}
    @if($history->isEmpty())
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 px-6 py-16 text-center">
            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                <i class="ph ph-clock-clockwise text-3xl text-slate-300"></i>
            </div>
            <p class="text-base font-bold text-slate-800">Belum Ada Riwayat</p>
            <p class="text-sm text-slate-500 mt-1">Riwayat akan muncul setelah Anda memproses transaksi.</p>
        </div>
    @else
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600">
                    <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                        <tr>
                            <th class="px-5 py-4">TANGGAL PROSES</th>
                            <th class="px-5 py-4">PROYEK & KATEGORI</th>
                            <th class="px-5 py-4">NOMINAL</th>
                            <th class="px-5 py-4">PENGAJU</th>
                            <th class="px-5 py-4 text-center">STATUS</th>
                            <th class="px-5 py-4">CATATAN</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($history as $trx)
                            @php $isApproved = $trx->status === 'approved';
                            $lastRejection = $trx->rejections->last(); @endphp
                            <tr class="hover:bg-slate-50 transition-colors">

                                {{-- Tanggal Proses --}}
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="font-medium text-slate-800">
                                        {{ \Carbon\Carbon::parse($trx->updated_at)->format('d M Y') }}</div>
                                    <div class="text-xs text-slate-400 mt-0.5">
                                        {{ \Carbon\Carbon::parse($trx->updated_at)->format('H:i') }} WITA</div>
                                </td>

                                {{-- Proyek & Kategori --}}
                                <td class="px-5 py-4">
                                    <div class="font-medium text-slate-800">{{ $trx->project->project_name ?? '-' }}</div>
                                    <div class="text-xs text-slate-500 mt-0.5">{{ $trx->category->category_name ?? '-' }}</div>
                                    @if($trx->description)
                                        <div class="text-[12px] text-slate-400 mt-1 italic">{{ Str::limit($trx->description, 45) }}
                                        </div>
                                    @endif
                                    @if($trx->nota_merah_id)
                                        <span
                                            class="inline-flex items-center gap-1 text-[10px] font-bold px-2 py-0.5 rounded-full bg-red-50 text-red-500 border border-red-100 mt-1">
                                            <i class="ph ph-note-pencil"></i> Nota Merah
                                        </span>
                                    @endif
                                </td>

                                {{-- Nominal --}}
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <span class="font-bold {{ $trx->type === 'pemasukan' ? 'text-emerald-600' : 'text-red-600' }}">
                                        {{ $trx->type === 'pemasukan' ? '+' : '-' }} Rp
                                        {{ number_format($trx->amount, 2, ',', '.') }}
                                    </span>
                                    <div class="flex items-center gap-1.5 mt-0.5">
                                        <span
                                            class="text-[10px] uppercase font-bold tracking-wide px-2 py-0.5 rounded
                                                {{ $trx->type === 'pemasukan' ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600' }}">
                                            {{ $trx->type }}
                                        </span>
                                        @if($trx->payment_method)
                                            <span
                                                class="text-[10px] uppercase font-medium text-slate-400">{{ $trx->payment_method }}</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Pengaju --}}
                                <td class="px-5 py-4 whitespace-nowrap">
                                    <div class="flex items-center gap-2">
                                        <div
                                            class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xs shrink-0">
                                            {{ substr($trx->user->name ?? 'U', 0, 1) }}
                                        </div>
                                        <div>
                                            <div class="font-medium text-slate-700 text-sm">{{ $trx->user->name ?? '-' }}</div>
                                            @if($trx->approver)
                                                <div class="text-xs text-slate-400">{{ $isApproved ? '✓' : '✗' }}
                                                    {{ $trx->approver->name }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- Status --}}
                                <td class="px-5 py-4 text-center whitespace-nowrap">
                                    @if($isApproved)
                                        <span
                                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">
                                            <i class="ph ph-check-circle"></i> Disetujui
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">
                                            <i class="ph ph-x-circle"></i> Ditolak
                                        </span>
                                    @endif
                                </td>

                                {{-- Catatan / Bukti --}}
                                <td class="px-5 py-4">
                                    @if(!$isApproved && $lastRejection)
                                        <div class="text-xs text-red-600 leading-relaxed max-w-[180px]">
                                            <i class="ph ph-warning text-red-400"></i>
                                            {{ Str::limit($lastRejection->reason, 70) }}
                                        </div>
                                    @elseif($trx->receipt_photo)
                                        @if(str_ends_with(strtolower($trx->receipt_photo), '.pdf'))
                                            <a href="{{ asset('storage/' . $trx->receipt_photo) }}" target="_blank"
                                                class="inline-flex items-center gap-1 text-[11px] font-semibold text-red-600 bg-red-50 px-2.5 py-1 rounded-lg hover:bg-red-100 border border-red-100">
                                                <i class="ph ph-file-pdf"></i> PDF
                                            </a>
                                        @else
                                            <a href="{{ asset('storage/' . $trx->receipt_photo) }}" target="_blank">
                                                <img src="{{ asset('storage/' . $trx->receipt_photo) }}" alt="Bukti"
                                                    class="h-12 w-16 object-cover rounded-lg border border-slate-200 hover:opacity-80 transition-opacity">
                                            </a>
                                        @endif
                                    @else
                                        <span class="text-xs text-slate-400 italic">—</span>
                                    @endif
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if($history->hasPages())
                <div class="px-6 py-4 border-t border-slate-100 text-sm flex items-center justify-between flex-wrap gap-3">
                    <span class="text-slate-500 text-xs">
                        Menampilkan {{ $history->firstItem() }}–{{ $history->lastItem() }} dari {{ $history->total() }} riwayat
                    </span>
                    {{ $history->links() }}
                </div>
            @endif
        </div>
    @endif

    {{-- Hidden Reject Form --}}
    <form id="rejectForm" method="POST" action="" class="hidden">
        @csrf
        <input type="hidden" name="reason" id="rejectReason">
    </form>

    <script>
        function rejectTransaction(id) {
            Swal.fire({
                title: 'Tolak Transaksi',
                text: 'Berikan alasan detail kenapa transaksi ini ditolak.',
                input: 'textarea',
                inputPlaceholder: 'Ketik alasan penolakan di sini...',
                showCancelButton: true,
                confirmButtonText: 'Tolak Pengajuan',
                confirmButtonColor: '#ef4444',
                cancelButtonText: 'Batal',
                inputAttributes: { style: 'min-height: 80px' },
                inputValidator: (value) => {
                    if (!value) return 'Alasan penolakan tidak boleh kosong!'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('rejectForm');
                    form.action = `/transactions/${id}/reject`;
                    document.getElementById('rejectReason').value = result.value;
                    form.submit();
                }
            });
        }
    </script>
@endsection