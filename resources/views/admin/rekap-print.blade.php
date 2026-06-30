<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Rekapitulasi Transaksi - SanthiGraha</title>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            size: A4 landscape;
            margin: 15mm;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #1e293b;
            background: #fff;
        }

        /* ===== HEADER ===== */

        .print-header {
            margin-bottom: 18px;
        }

        .print-header h1 {
            text-align: center;
            font-size: 20px;
            font-weight: bold;
            letter-spacing: 0.5px;
        }

        .subtitle {
            text-align: center;
            font-size: 13px;
            margin-top: 2px;
            margin-bottom: 12px;
            color: #475569;
        }

        .divider {
            border-bottom: 2px solid #000;
            margin-bottom: 14px;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11.5px;
        }

        .meta-table td {
            padding: 2px 0;
            vertical-align: top;
        }

        .meta-table td:first-child {
            width: 130px;
            color: #475569;
        }

        .meta-table td:nth-child(2) {
            width: 12px;
        }

        /* ===== MAIN TABLE ===== */

        table.print-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 16px;
            font-size: 11px;
            table-layout: fixed;
        }

        table.print-table th {
            border: 1px solid #94a3b8;
            background: #f1f5f9;
            padding: 8px 6px;
            text-align: center;
            font-weight: bold;
            font-size: 10.5px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            color: #334155;
        }

        table.print-table td {
            border: 1px solid #cbd5e1;
            padding: 7px 6px;
            vertical-align: middle;
            overflow-wrap: break-word;
            word-break: break-word;
        }

        table.print-table tbody tr:nth-child(even) {
            background: #f8fafc;
        }

        table.print-table td.text-center {
            text-align: center;
        }

        table.print-table td.text-right {
            text-align: right;
        }

        .empty-row td {
            text-align: center;
            padding: 30px;
            color: #64748b;
            font-style: italic;
            border: 1px solid #cbd5e1;
        }

        /* ===== BADGES ===== */

        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 9.5px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            white-space: nowrap;
        }

        .badge-income {
            background: #d1fae5;
            color: #065f46;
        }

        .badge-expense {
            background: #fee2e2;
            color: #991b1b;
        }

        .badge-method {
            background: #f1f5f9;
            color: #475569;
        }

        .amount {
            font-weight: bold;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
        }

        .amount-income {
            color: #059669;
        }

        .amount-expense {
            color: #dc2626;
        }

        /* ===== SUMMARY ===== */

        table.summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 0;
            table-layout: fixed;
            page-break-inside: avoid;
        }

        table.summary-table td {
            border: 1px solid #cbd5e1;
            border-top: none;
            padding: 8px 6px;
        }

        table.summary-table tr:last-child td {
            border-bottom: 1px solid #94a3b8;
        }

        .summary-table .label {
            text-align: right;
            font-weight: bold;
            font-size: 11.5px;
        }

        .summary-table .value {
            text-align: right;
            font-weight: bold;
            font-size: 11.5px;
            white-space: nowrap;
        }

        .summary-table .pemasukan {
            color: #047857;
        }

        .summary-table .pengeluaran {
            color: #b91c1c;
        }

        .summary-table .saldo {
            color: #1e3a8a;
            font-size: 13px;
            background: #eef2ff;
        }

        /* ===== SIGNATURE ===== */

        .signature-area {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
            page-break-inside: avoid;
        }

        .signature-box {
            width: 220px;
            text-align: center;
            font-size: 11.5px;
        }

        .signature-line {
            height: 60px;
            border-bottom: 1px solid #000;
            margin: 8px 0;
        }

        .signature-name {
            font-weight: bold;
        }

        .signature-title {
            margin-top: 3px;
            color: #475569;
        }
    </style>

</head>

<body>

    <div class="print-header">

        <h1>CV SANTHI GRAHA</h1>
        <p class="subtitle">LAPORAN REKAPITULASI TRANSAKSI</p>

        <div class="divider"></div>

        <table class="meta-table">

            <tr>
                <td>Periode</td>
                <td>:</td>
                <td>
                    {{ request('date_from')
    ? \Carbon\Carbon::parse(request('date_from'))->format('d M Y')
    : 'Semua (Awal)' }}
                    &mdash;
                    {{ request('date_to')
    ? \Carbon\Carbon::parse(request('date_to'))->format('d M Y')
    : 'Semua (Sekarang)' }}
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
                    <td style="text-transform:capitalize">{{ request('type') }}</td>
                </tr>
            @endif

            <tr>
                <td>Dicetak Oleh</td>
                <td>:</td>
                <td>{{ auth()->user()->name }}</td>
            </tr>

            <tr>
                <td>Tanggal Cetak</td>
                <td>:</td>
                <td>{{ date('d M Y, H:i') }} WITA</td>
            </tr>

        </table>

    </div>

    <table class="print-table">

        <colgroup>
            <col style="width:35px">
            <col style="width:80px">
            <col style="width:17%">
            <col style="width:17%">
            <col style="width:18%">
            <col style="width:75px">
            <col style="width:90px">
            <col style="width:150px">
        </colgroup>

        <thead>
            <tr>
                <th>No</th>
                <th>Tanggal</th>
                <th>Proyek</th>
                <th>Kategori</th>
                <th>Deskripsi</th>
                <th>Tipe</th>
                <th>Metode</th>
                <th>Nominal</th>
            </tr>
        </thead>

        <tbody>
            @forelse($transactions as $index => $trx)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ \Carbon\Carbon::parse($trx->transaction_date)->format('d/m/Y') }}</td>
                    <td>{{ $trx->project->project_name ?? '-' }}</td>
                    <td>{{ $trx->category->category_name ?? '-' }}</td>
                    <td>{{ $trx->description ?: '-' }}</td>
                    <td class="text-center">
                        @if($trx->type == 'pemasukan')
                            <span class="badge badge-income">{{ $trx->type }}</span>
                        @else
                            <span class="badge badge-expense">{{ $trx->type }}</span>
                        @endif
                    </td>
                    <td class="text-center">
                        <span class="badge badge-method">{{ $trx->payment_method ?? '-' }}</span>
                    </td>
                    <td class="text-right">
                        <span class="amount {{ $trx->type == 'pemasukan' ? 'amount-income' : 'amount-expense' }}">
                            Rp {{ number_format($trx->amount, 2, ',', '.') }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr class="empty-row">
                    <td colspan="8">Tidak ada data transaksi.</td>
                </tr>
            @endforelse
        </tbody>

    </table>

    @if($transactions->count())

        @php $filterType = request('type'); @endphp

        <table class="summary-table">

            <colgroup>
                <col style="width:35px">
                <col style="width:80px">
                <col style="width:17%">
                <col style="width:17%">
                <col style="width:18%">
                <col style="width:75px">
                <col style="width:90px">
                <col style="width:150px">
            </colgroup>

            @if(!$filterType || $filterType == 'pemasukan')
                <tr>
                    <td class="label pemasukan" colspan="7">TOTAL PEMASUKAN</td>
                    <td class="value pemasukan">Rp {{ number_format($totalPemasukan, 2, ',', '.') }}</td>
                </tr>
            @endif

            @if(!$filterType || $filterType == 'pengeluaran')
                <tr>
                    <td class="label pengeluaran" colspan="7">TOTAL PENGELUARAN</td>
                    <td class="value pengeluaran">Rp {{ number_format($totalPengeluaran, 2, ',', '.') }}</td>
                </tr>
            @endif

            @if(!$filterType)
                <tr>
                    <td class="label saldo" colspan="7">SALDO</td>
                    <td class="value saldo">Rp {{ number_format($saldo, 2, ',', '.') }}</td>
                </tr>
            @endif

        </table>

    @endif

    <div class="signature-area">

        <div class="signature-box">
            <p>Mengetahui,</p>
            <div class="signature-line"></div>
            <p>(...........................................)</p>
            <p class="signature-title">Pimpinan</p>
        </div>

        <div class="signature-box">
            <p>Dibuat oleh,</p>
            <div class="signature-line"></div>
            <p class="signature-name">( {{ auth()->user()->name }} )</p>
            <p class="signature-title">{{ ucfirst(auth()->user()->role) }}</p>
        </div>

    </div>

    <script>
        window.onload = function () {
            window.print();
        };
        window.onafterprint = function () {
            window.close();
        };
    </script>

</body>

</html>