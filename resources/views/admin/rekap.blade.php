@extends('layouts.admin')

@section('title', 'Rekap & Laporan - SanthiGraha')
@section('page_title', 'Rekapitulasi & Laporan Transaksi')

@section('content')
    <!-- Summary Cards -->
    @php $filterType = request('type'); @endphp
    <div class="grid grid-cols-1 md:grid-cols-{{ $filterType ? '2' : '4' }} gap-5 mb-6 no-print">
        @if(!$filterType || $filterType == 'pemasukan')
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center gap-4 min-w-0">
                <div class="w-12 h-12 rounded-full bg-emerald-50 flex items-center justify-center text-emerald-500 shrink-0">
                    <i class="ph ph-trend-up text-xl"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-medium text-slate-500 mb-0.5">Total Pemasukan</p>
                    <h3 class="text-lg font-bold text-emerald-600 whitespace-nowrap">Rp
                        {{ number_format($totalPemasukan, 0, ',', '.') }}</h3>
                </div>
            </div>
        @endif
        @if(!$filterType || $filterType == 'pengeluaran')
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center gap-4 min-w-0">
                <div class="w-12 h-12 rounded-full bg-red-50 flex items-center justify-center text-red-500 shrink-0">
                    <i class="ph ph-trend-down text-xl"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-medium text-slate-500 mb-0.5">Total Pengeluaran</p>
                    <h3 class="text-lg font-bold text-red-600 whitespace-nowrap">Rp
                        {{ number_format($totalPengeluaran, 0, ',', '.') }}</h3>
                </div>
            </div>
        @endif
        @if(!$filterType)
            <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center gap-4 min-w-0">
                <div class="w-12 h-12 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-500 shrink-0">
                    <i class="ph ph-wallet text-xl"></i>
                </div>
                <div class="min-w-0">
                    <p class="text-sm font-medium text-slate-500 mb-0.5">Saldo</p>
                    <h3 class="text-lg font-bold {{ $saldo >= 0 ? 'text-indigo-600' : 'text-red-600' }} whitespace-nowrap">Rp
                        {{ number_format($saldo, 0, ',', '.') }}</h3>
                </div>
            </div>
        @endif
        <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center gap-4 min-w-0">
            <div class="w-12 h-12 rounded-full bg-amber-50 flex items-center justify-center text-amber-500 shrink-0">
                <i class="ph ph-receipt text-xl"></i>
            </div>
            <div class="min-w-0">
                <p class="text-sm font-medium text-slate-500 mb-0.5">Jumlah Transaksi</p>
                <h3 class="text-lg font-bold text-slate-800 whitespace-nowrap">{{ $totalTransaksi }}</h3>
            </div>
        </div>
    </div>

    <!-- Filter Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 mb-6 overflow-hidden no-print">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center gap-3">
            <div class="w-8 h-8 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-500">
                <i class="ph ph-funnel text-base"></i>
            </div>
            <h3 class="text-sm font-bold text-slate-700">Filter Laporan</h3>
        </div>
        <div class="p-6">
            <form action="{{ route('rekap.index') }}" method="GET">
                <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4 mb-4">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wider">Dari
                            Tanggal</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                            class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wider">Sampai
                            Tanggal</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                            class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none">
                    </div>
                    <div>
                        <label
                            class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wider">Proyek</label>
                        <select name="project_id"
                            class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none cursor-pointer">
                            <option value="">Semua Proyek</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->id }}" {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                    {{ $project->project_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wider">Kategori</label>
                        <select name="category_id"
                            class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none cursor-pointer">
                            <option value="">Semua Kategori</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->category_name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label
                            class="block text-xs font-semibold text-slate-500 mb-1.5 uppercase tracking-wider">Tipe</label>
                        <select name="type"
                            class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-sm focus:ring-2 focus:ring-brand-500/20 focus:border-brand-500 outline-none cursor-pointer">
                            <option value="">Semua Tipe</option>
                            <option value="pemasukan" {{ request('type') == 'pemasukan' ? 'selected' : '' }}>Pemasukan
                            </option>
                            <option value="pengeluaran" {{ request('type') == 'pengeluaran' ? 'selected' : '' }}>Pengeluaran
                            </option>
                        </select>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <button type="submit"
                        class="px-5 py-2 bg-brand-600 hover:bg-brand-700 text-white text-sm font-semibold rounded-xl shadow-lg shadow-brand-500/30 transition-all flex items-center gap-2">
                        <i class="ph ph-funnel text-base"></i>
                        Terapkan Filter
                    </button>
                    @if(request()->anyFilled(['date_from', 'date_to', 'project_id', 'category_id', 'type']))
                        <a href="{{ route('rekap.index') }}"
                            class="px-5 py-2 bg-red-50 hover:bg-red-100 text-red-600 text-sm font-semibold rounded-xl transition-colors flex items-center gap-2">
                            <i class="ph ph-x text-base"></i>
                            Reset Filter
                        </a>
                    @endif
                    <div class="ml-auto flex items-center gap-2">
                        <a href="{{ route('rekap.export', request()->query()) }}"
                            class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl transition-colors flex items-center gap-2">
                            <i class="ph ph-file-csv text-base"></i>
                            Export Excel / CSV
                        </a>
                        <button type="button" onclick="window.print()"
                            class="px-5 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-semibold rounded-xl transition-colors flex items-center gap-2">
                            <i class="ph ph-printer text-base"></i>
                            Cetak PDF / Print
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- ==================== PRINT AREA ==================== -->
    <div id="printArea">

        <!-- Print Header (only visible when printing) -->
        <div class="print-header">
            <h1>CV SANTHI GRAHA</h1>
            <p class="subtitle">LAPORAN REKAPITULASI TRANSAKSI</p>
            <div class="divider"></div>
            <div class="meta-info">
                <table class="meta-table">
                    <tr>
                        <td style="width:120px;">Periode</td>
                        <td style="width:10px;">:</td>
                        <td>
                            {{ request('date_from') ? \Carbon\Carbon::parse(request('date_from'))->format('d M Y') : 'Semua (Awal)' }}
                            &mdash;
                            {{ request('date_to') ? \Carbon\Carbon::parse(request('date_to'))->format('d M Y') : 'Semua (Sekarang)' }}
                        </td>
                    </tr>
                    @if(request()->filled('project_id'))
                        @php $filteredProject = $projects->firstWhere('id', request('project_id')); @endphp
                        <tr>
                            <td>Proyek</td>
                            <td>:</td>
                            <td>{{ $filteredProject->project_name ?? '-' }}</td>
                        </tr>
                    @endif
                    @if(request()->filled('category_id'))
                        @php $filteredCategory = $categories->firstWhere('id', request('category_id')); @endphp
                        <tr>
                            <td>Kategori</td>
                            <td>:</td>
                            <td>{{ $filteredCategory->category_name ?? '-' }}</td>
                        </tr>
                    @endif
                    @if(request()->filled('type'))
                        <tr>
                            <td>Tipe Transaksi</td>
                            <td>:</td>
                            <td style="text-transform:capitalize;">{{ request('type') }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td>Dicetak Oleh</td>
                        <td>:</td>
                        <td>{{ auth()->user()->name ?? 'Admin' }}</td>
                    </tr>
                    <tr>
                        <td>Tanggal Cetak</td>
                        <td>:</td>
                        <td>{{ date('d M Y, H:i') }} WITA</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Screen Table Header (hidden on print) -->
        <div class="bg-white rounded-t-2xl px-6 py-4 border-b border-slate-100 flex items-center justify-between no-print">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-500">
                    <i class="ph ph-table text-base"></i>
                </div>
                <h3 class="text-sm font-bold text-slate-700">Detail Transaksi</h3>
            </div>
            <span class="text-xs font-medium text-slate-400">{{ $totalTransaksi }} transaksi ditemukan</span>
        </div>

        <!-- Data Table -->
        <div
            class="bg-white rounded-b-2xl shadow-sm border border-t-0 border-slate-100 overflow-hidden print-table-container">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-slate-600 print-table" id="rekapTable">
                    <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100">
                        <tr>
                            <th class="px-6 py-4 w-12 text-center">NO</th>
                            <th class="px-6 py-4">TANGGAL</th>
                            <th class="px-6 py-4">PROYEK</th>
                            <th class="px-6 py-4">KATEGORI</th>
                            <th class="px-6 py-4">DESKRIPSI</th>
                            <th class="px-6 py-4 text-center">TIPE</th>
                            <th class="px-6 py-4 text-center">METODE</th>
                            <th class="px-6 py-4 text-right">NOMINAL</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($transactions as $index => $trx)
                            <tr class="hover:bg-slate-50 transition-colors">
                                <td class="px-6 py-4 text-center">
                                    {{ $index + 1 + ($transactions->currentPage() - 1) * $transactions->perPage() }}</td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    {{ \Carbon\Carbon::parse($trx->transaction_date)->format('d/m/Y') }}</td>
                                <td class="px-6 py-4 font-medium text-slate-800">{{ $trx->project->project_name ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $trx->category->category_name ?? '-' }}</td>
                                <td class="px-6 py-4">{{ $trx->description ?: '-' }}</td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <span
                                        class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] uppercase font-bold tracking-wider {{ $trx->type == 'pemasukan' ? 'bg-emerald-100 text-emerald-600' : 'bg-red-100 text-red-600' }}">
                                        {{ $trx->type }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-center whitespace-nowrap">
                                    <span
                                        class="text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded bg-slate-100 text-slate-600 border border-slate-200">
                                        {{ $trx->payment_method ?? '-' }}
                                    </span>
                                </td>
                                <td
                                    class="px-6 py-4 whitespace-nowrap text-right font-bold {{ $trx->type == 'pemasukan' ? 'text-emerald-600' : 'text-red-600' }}">
                                    Rp {{ number_format($trx->amount, 0, ',', '.') }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-6 py-12 text-center text-slate-400">
                                    <i class="ph ph-chart-bar text-5xl mb-4 text-slate-200 no-print"></i>
                                    <p class="text-base font-medium text-slate-500">Tidak ada data transaksi</p>
                                    <p class="text-sm">Coba ubah filter untuk menampilkan data.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($transactions->count() > 0)
                        <tfoot>
                            @if(!$filterType || $filterType == 'pemasukan')
                                <tr class="bg-emerald-50 border-t-2 border-slate-200">
                                    <td colspan="7"
                                        class="px-6 py-3 text-right font-bold text-slate-600 text-xs uppercase tracking-wider">Total
                                        Pemasukan</td>
                                    <td class="px-6 py-3 text-right font-bold text-emerald-600 whitespace-nowrap">Rp
                                        {{ number_format($totalPemasukan, 0, ',', '.') }}</td>
                                </tr>
                            @endif
                            @if(!$filterType || $filterType == 'pengeluaran')
                                <tr class="bg-red-50">
                                    <td colspan="7"
                                        class="px-6 py-3 text-right font-bold text-slate-600 text-xs uppercase tracking-wider">Total
                                        Pengeluaran</td>
                                    <td class="px-6 py-3 text-right font-bold text-red-600 whitespace-nowrap">Rp
                                        {{ number_format($totalPengeluaran, 0, ',', '.') }}</td>
                                </tr>
                            @endif
                            @if(!$filterType)
                                <tr class="bg-indigo-50 border-t-2 border-indigo-200">
                                    <td colspan="7"
                                        class="px-6 py-3 text-right font-bold text-slate-800 text-sm uppercase tracking-wider">SALDO
                                    </td>
                                    <td class="px-6 py-3 text-right font-bold text-indigo-700 text-base whitespace-nowrap">Rp
                                        {{ number_format($saldo, 0, ',', '.') }}</td>
                                </tr>
                            @endif
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>

        <!-- Pagination (screen only) -->
        @if($transactions->hasPages())
            <div class="bg-white rounded-2xl mt-4 px-6 py-4 border border-slate-100 text-sm no-print">
                {{ $transactions->links() }}
            </div>
        @endif

        <!-- Print Footer (only visible when printing) -->
        <div class="print-footer">
            <div class="signature-area">
                <div class="signature-box">
                    <p>Mengetahui,</p>
                    <div class="signature-line"></div>
                    <p class="signature-name">( ............................ )</p>
                    <p class="signature-title">Pimpinan</p>
                </div>
                <div class="signature-box">
                    <p>Dibuat oleh,</p>
                    <div class="signature-line"></div>
                    <p class="signature-name">( {{ auth()->user()->name ?? 'Admin' }} )</p>
                    <p class="signature-title">{{ ucfirst(auth()->user()->role ?? 'Admin') }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ==================== PRINT STYLES ==================== -->
    <style>
        /* Hide print-only elements on screen */
        .print-header,
        .print-footer {
            display: none;
        }

        @media print {

            /* Page settings */
            @page {
                size: A4 landscape;
                margin: 10mm 10mm 10mm 10mm;
            }

            /* Reset everything */
            html,
            body {
                width: 100% !important;
                height: auto !important;
                margin: 0 !important;
                padding: 0 !important;
                overflow: visible !important;
                background: #fff !important;
                display: block !important;
            }

            /* Kill sidebar and all non-print content */
            body>* {
                display: none !important;
                visibility: hidden !important;
            }

            /* Hide non-print elements */
            .no-print,
            aside,
            header,
            nav {
                display: none !important;
            }

            /* Show print area as full-page block */
            #printArea,
            #printArea *,
            #printArea~style {
                visibility: visible !important;
            }

            body>main,
            body>main>*,
            body>div {
                display: block !important;
                visibility: visible !important;
            }

            #printArea {
                display: block !important;
                position: fixed;
                left: 0;
                top: 0;
                right: 0;
                width: 100% !important;
                max-width: none !important;
                padding: 0 !important;
                margin: 0 !important;
                background: #fff !important;
                z-index: 99999;
            }

            /* ====== PRINT HEADER ====== */
            .print-header {
                display: block !important;
                margin-bottom: 16px;
                font-family: 'Inter', 'Segoe UI', Arial, sans-serif;
            }

            .print-header h1 {
                text-align: center;
                font-size: 20px;
                font-weight: 800;
                letter-spacing: 3px;
                margin: 0 0 2px 0;
                color: #000;
            }

            .print-header .subtitle {
                text-align: center;
                font-size: 12px;
                font-weight: 600;
                letter-spacing: 1.5px;
                color: #333;
                margin: 0 0 8px 0;
            }

            .print-header .divider {
                height: 3px;
                background: #000;
                border: none;
                margin: 0;
            }

            .print-header .divider::after {
                content: '';
                display: block;
                height: 1.5px;
                background: #000;
                margin-top: 2px;
            }

            .print-header .meta-info {
                margin-top: 12px;
            }

            .print-header .meta-table {
                font-size: 10px;
                color: #222;
                border-collapse: collapse;
                border: none;
                width: auto;
            }

            .print-header .meta-table td {
                padding: 1.5px 6px 1.5px 0;
                border: none !important;
                font-size: 10px;
                line-height: 1.5;
                vertical-align: top;
                background: transparent !important;
            }

            /* ====== DATA TABLE ====== */
            .print-table-container {
                border: none !important;
                box-shadow: none !important;
                border-radius: 0 !important;
                overflow: visible !important;
                background: transparent !important;
            }

            .print-table-container>div {
                overflow: visible !important;
            }

            .print-table {
                width: 100% !important;
                border-collapse: collapse !important;
                table-layout: fixed !important;
                font-size: 9px !important;
                color: #000 !important;
                background: #fff !important;
            }

            /* Column widths */
            .print-table th:nth-child(1) {
                width: 30px !important;
            }

            /* NO */
            .print-table th:nth-child(2) {
                width: 70px !important;
            }

            /* TANGGAL */
            .print-table th:nth-child(3) {
                width: 100px !important;
            }

            /* PROYEK */
            .print-table th:nth-child(4) {
                width: 80px !important;
            }

            /* KATEGORI */
            .print-table th:nth-child(5) {
                width: auto !important;
            }

            /* DESKRIPSI */
            .print-table th:nth-child(6) {
                width: auto !important;
            }

            /* TIPE */
            .print-table th:nth-child(7) {
                width: auto !important;
            }

            /* METODE */
            .print-table th:nth-child(8) {
                width: auto !important;
            }

            /* NOMINAL */

            .print-table thead tr {
                background: #e2e8f0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .print-table th {
                border: 1.5px solid #64748b !important;
                padding: 6px 6px !important;
                font-size: 8px !important;
                font-weight: 700 !important;
                text-transform: uppercase !important;
                letter-spacing: 0.3px !important;
                color: #000 !important;
                text-align: left !important;
                background: #e2e8f0 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
                white-space: nowrap !important;
            }

            .print-table th:first-child,
            .print-table td:first-child {
                text-align: center !important;
            }

            .print-table th:nth-child(6),
            .print-table td:nth-child(6),
            .print-table th:nth-child(7),
            .print-table td:nth-child(7) {
                text-align: center !important;
            }

            .print-table th:last-child,
            .print-table td:last-child {
                text-align: right !important;
            }

            .print-table td {
                border: 1px solid #94a3b8 !important;
                padding: 5px 6px !important;
                font-size: 9px !important;
                color: #111 !important;
                vertical-align: top !important;
                word-wrap: break-word !important;
                overflow-wrap: break-word !important;
                background: #fff !important;
            }

            .print-table td:last-child {
                white-space: nowrap !important;
                font-weight: 600 !important;
            }

            .print-table tbody tr:nth-child(even) td {
                background: #f8fafc !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            /* Reset inline badge styles for print */
            .print-table span {
                background: transparent !important;
                border: none !important;
                padding: 0 !important;
                font-size: 9px !important;
                color: #111 !important;
                font-weight: 600 !important;
            }

            /* ====== FOOTER TOTALS ====== */
            .print-table tfoot tr {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .print-table tfoot td {
                border: 1.5px solid #64748b !important;
                padding: 6px 6px !important;
                font-size: 9px !important;
                font-weight: 700 !important;
                color: #000 !important;
                background: #f1f5f9 !important;
                white-space: nowrap !important;
            }

            .print-table tfoot tr:last-child td {
                font-size: 10px !important;
                font-weight: 800 !important;
                border-top: 2.5px solid #000 !important;
                background: #e2e8f0 !important;
            }

            /* Force all text colors to black for print */
            .text-emerald-600,
            .text-red-600,
            .text-indigo-700,
            .text-indigo-600,
            .text-slate-600,
            .text-slate-800 {
                color: #000 !important;
            }

            /* ====== SIGNATURE / FOOTER ====== */
            .print-footer {
                display: block !important;
                margin-top: 30px;
                page-break-inside: avoid;
            }

            .signature-area {
                display: flex !important;
                justify-content: space-between;
                padding: 0 60px;
            }

            .signature-box {
                text-align: center;
                font-size: 10px;
                color: #222;
            }

            .signature-box p {
                margin: 0;
            }

            .signature-line {
                width: 160px;
                height: 50px;
                margin: 0 auto;
            }

            .signature-name {
                font-weight: 700;
                margin-top: 2px !important;
            }

            .signature-title {
                font-size: 9px;
                color: #555;
            }

            /* Kill all Tailwind decorative stuff */
            .rounded-2xl,
            .rounded-t-2xl,
            .rounded-b-2xl,
            .shadow-sm,
            .shadow-lg {
                border-radius: 0 !important;
                box-shadow: none !important;
            }

            .border-slate-100,
            .border-indigo-200 {
                border-color: transparent !important;
            }

            /* Backgrounds reset */
            .bg-white,
            .bg-slate-50,
            .bg-emerald-50,
            .bg-red-50,
            .bg-indigo-50 {
                background: transparent !important;
            }
        }
    </style>
@endsection