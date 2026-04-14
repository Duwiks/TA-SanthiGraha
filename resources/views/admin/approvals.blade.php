@extends('layouts.admin')

@section('title', 'Menu Approval - SanthiGraha')
@section('page_title', 'Antrean Persetujuan Transaksi')

@section('content')
    <div class="mb-6">
        <h2 class="text-lg font-bold text-slate-800">Menunggu Validasi</h2>
        <p class="text-sm text-slate-500 mt-1">Berikut adalah daftar transaksi yang diajukan oleh pegawai dan butuh persetujuan Anda.</p>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm text-slate-600">
                <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                    <tr>
                        <th class="px-6 py-4">TANGGAL PENGAJUAN</th>
                        <th class="px-6 py-4">PROYEK & KATEGORI</th>
                        <th class="px-6 py-4">NOMINAL (TIPE)</th>
                        <th class="px-6 py-4">PENGAJU</th>
                        <th class="px-6 py-4 text-center">TINDAKAN</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($transactions as $trx)
                    <tr class="hover:bg-slate-50 transition-colors">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="flex flex-col">
                                <span class="font-medium text-slate-800">{{ \Carbon\Carbon::parse($trx->created_at)->format('d M Y, H:i') }}</span>
                                <span class="text-xs text-slate-400 mt-1">Trx: {{ \Carbon\Carbon::parse($trx->transaction_date)->format('d/m/Y') }}</span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="font-medium text-slate-800">{{ $trx->project->project_name ?? '-' }}</div>
                            <div class="text-xs text-slate-500 mt-0.5">{{ $trx->category->category_name ?? '-' }}</div>
                            @if($trx->description)
                                <div class="text-[12px] text-slate-400 mt-1 italic leading-relaxed">{{ $trx->description }}</div>
                            @endif
                            @if($trx->receipt_photo)
                                <div class="mt-3">
                                    @if(str_ends_with(strtolower($trx->receipt_photo), '.pdf'))
                                        <a href="{{ asset('storage/' . $trx->receipt_photo) }}" target="_blank" class="text-[11px] font-bold text-red-600 bg-red-50 hover:bg-red-100 inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg transition-colors w-max border border-red-100">
                                            <i class="ph ph-file-pdf text-base"></i> Dokumen PDF
                                        </a>
                                    @else
                                        <a href="{{ asset('storage/' . $trx->receipt_photo) }}" target="_blank" class="block inline-block">
                                            <img src="{{ asset('storage/' . $trx->receipt_photo) }}" alt="Bukti Transaksi" class="h-16 w-24 object-cover rounded-lg border border-slate-200 shadow-sm hover:opacity-80 transition-opacity" title="Klik untuk perbesar">
                                        </a>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-bold {{ $trx->type == 'pemasukan' ? 'text-emerald-600' : 'text-red-600' }}">
                                Rp {{ number_format($trx->amount, 0, ',', '.') }}
                            </span>
                            <div class="flex items-center gap-2 mt-1.5">
                                <div class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded {{ $trx->type == 'pemasukan' ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600' }}">
                                    {{ $trx->type }}
                                </div>
                                @if($trx->payment_method)
                                    <div class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200" title="Metode Transfer/Pencairan">
                                        <i class="ph ph-wallet"></i> {{ $trx->payment_method }}
                                    </div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="font-medium flex items-center gap-2">
                                <div class="w-8 h-8 rounded-full bg-brand-100 text-brand-600 flex items-center justify-center font-bold text-xs">
                                    {{ substr($trx->user->name ?? 'U', 0, 1) }}
                                </div>
                                {{ $trx->user->name ?? '-' }}
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-center">
                            <div class="flex items-center justify-center gap-3">
                                <form action="{{ route('transactions.approve', $trx->id) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-500 text-white font-medium text-sm flex items-center gap-2 hover:bg-emerald-600 hover:shadow-lg hover:shadow-emerald-500/20 transition-all" title="Setujui (Approve)">
                                        <i class="ph ph-check-circle text-lg"></i> Setujui
                                    </button>
                                </form>
                                
                                <button onclick="rejectTransaction({{ $trx->id }})" class="px-4 py-2 rounded-xl bg-red-50 text-red-600 font-medium text-sm flex items-center gap-2 hover:bg-red-500 hover:text-white transition-all border border-red-100 hover:border-red-500" title="Tolak (Reject)">
                                    <i class="ph ph-x-circle text-lg"></i> Tolak
                                </button>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-16 text-center">
                            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto mb-4">
                                <i class="ph ph-check-square text-3xl text-slate-300"></i>
                            </div>
                            <p class="text-base font-bold text-slate-800">Antrean Bersih</p>
                            <p class="text-sm text-slate-500 mt-1">Tidak ada transaksi yang menunggu persetujuan saat ini.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination Links -->
        @if($transactions->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 text-sm">
            {{ $transactions->links() }}
        </div>
        @endif
    </div>

    <!-- Hidden form for rejection via SweetAlert2 -->
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
                inputValidator: (value) => {
                    if (!value) {
                        return 'Alasan penolakan tidak boleh kosong!'
                    }
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('rejectForm');
                    form.action = `/transactions/${id}/reject`; // Membidik endpoint TransactionController@reject
                    document.getElementById('rejectReason').value = result.value;
                    form.submit();
                }
            });
        }
    </script>
@endsection
