<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Project;
use App\Models\Category;

class RekapController extends Controller
{
    /**
     * Menampilkan halaman rekap & laporan transaksi.
     */
    public function index(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $projects = Project::orderBy('project_name')->get();
        $categories = Category::orderBy('category_name')->get();

        // Base query: hanya transaksi yang sudah disetujui
        $query = Transaction::where('status', 'approved')
            ->with(['user:id,name', 'project:id,project_name', 'category:id,category_name']);

        // Filter tanggal
        if ($request->filled('date_from')) {
            $query->where('transaction_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('transaction_date', '<=', $request->date_to);
        }

        // Filter proyek
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        // Filter kategori
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        // Filter tipe
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Hitung summary dari hasil filter (tanpa pagination)
        $summaryQuery = clone $query;
        $totalPemasukan = (clone $summaryQuery)->where('type', 'pemasukan')->sum('amount');
        $totalPengeluaran = (clone $summaryQuery)->where('type', 'pengeluaran')->sum('amount');
        $saldo = $totalPemasukan - $totalPengeluaran;
        $totalTransaksi = (clone $summaryQuery)->count();

        // Data untuk tabel
        $transactions = $query->orderBy('transaction_date', 'desc')->paginate(15)->withQueryString();

        return view('admin.rekap', compact(
            'projects',
            'categories',
            'transactions',
            'totalPemasukan',
            'totalPengeluaran',
            'saldo',
            'totalTransaksi',
        ));
    }

    /**
     * Export rekap data to CSV/Excel.
     */
    public function export(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        // Base query
        $query = Transaction::where('status', 'approved')
            ->with(['user:id,name', 'project:id,project_name', 'category:id,category_name']);

        // Apply same filters
        if ($request->filled('date_from')) {
            $query->where('transaction_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('transaction_date', '<=', $request->date_to);
        }
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')->get();

        $fileName = 'lap_transaksi_' . date('Y-m-d_His') . '.xls';

        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        $html .= '<head><meta charset="utf-8"></head><body>';
        $html .= '<h3 style="font-family: sans-serif; text-align: center;">REKAPITULASI TRANSAKSI - CV SANTHI GRAHA</h3>';
        
        // Cek filter info
        $html .= '<table style="font-family: sans-serif; font-size: 12px; margin-bottom: 20px;">';
        $html .= '<tr><td>Tanggal Cetak</td><td>: '.date('d M Y, H:i').'</td></tr>';
        if ($request->filled('date_from')) $html .= '<tr><td>Dari Tanggal</td><td>: '.\Carbon\Carbon::parse($request->date_from)->format('d M Y').'</td></tr>';
        if ($request->filled('date_to')) $html .= '<tr><td>Sampai Tanggal</td><td>: '.\Carbon\Carbon::parse($request->date_to)->format('d M Y').'</td></tr>';
        $html .= '</table>';

        $html .= '<table border="1" cellpadding="6" cellspacing="0" style="border-collapse: collapse; font-family: sans-serif; font-size: 12px; width: 100%;">';
        $html .= '<thead style="background-color: #e2e8f0; color: #000; font-weight: bold; text-align: center;">';
        $html .= '<tr>';
        $html .= '<th width="40">NO</th><th width="100">TANGGAL</th><th width="150">PROYEK</th><th width="120">KATEGORI</th><th width="200">DESKRIPSI</th><th width="90">TIPE</th><th width="90">METODE</th><th width="120">NOMINAL</th>';
        $html .= '</tr></thead><tbody>';

        $totalPemasukan = 0;
        $totalPengeluaran = 0;

        foreach ($transactions as $index => $trx) {
            $nom = $trx->amount;
            if ($trx->type == 'pemasukan') $totalPemasukan += $nom;
            if ($trx->type == 'pengeluaran') $totalPengeluaran += $nom;

            $bgRow = ($index % 2 == 0) ? '#ffffff' : '#f8fafc';
            $html .= '<tr style="background-color: '.$bgRow.';">';
            $html .= '<td align="center">'.($index + 1).'</td>';
            $html .= '<td>'.\Carbon\Carbon::parse($trx->transaction_date)->format('d/m/Y').'</td>';
            $html .= '<td>'.($trx->project->project_name ?? '-').'</td>';
            $html .= '<td>'.($trx->category->category_name ?? '-').'</td>';
            $html .= '<td>'.($trx->description ?: '-').'</td>';
            
            // Styled Type Column
            $bgType = $trx->type == 'pemasukan' ? '#d1fae5' : '#fee2e2';
            $colorType = $trx->type == 'pemasukan' ? '#047857' : '#b91c1c';
            $html .= '<td align="center" style="background-color: '.$bgType.'; color: '.$colorType.'; font-weight: bold;">'.strtoupper($trx->type).'</td>';
            
            $html .= '<td align="center" style="background-color: #f1f5f9; color: #475569; font-weight: bold;">'.strtoupper($trx->payment_method ?? '-').'</td>';
            $html .= '<td align="right" style="font-weight: bold;">Rp '.number_format($nom, 0, ',', '.').'</td>';
            $html .= '</tr>';
        }

        $html .= '</tbody><tfoot>';
        
        // Pemasukan Total
        if (!$request->filled('type') || $request->type == 'pemasukan') {
            $html .= '<tr>';
            $html .= '<td colspan="7" align="right" style="font-weight: bold; background-color: #d1fae5; color: #047857;">TOTAL PEMASUKAN</td>';
            $html .= '<td align="right" style="font-weight: bold; background-color: #d1fae5; color: #047857;">Rp '.number_format($totalPemasukan, 0, ',', '.').'</td>';
            $html .= '</tr>';
        }

        // Pengeluaran Total
        if (!$request->filled('type') || $request->type == 'pengeluaran') {
            $html .= '<tr>';
            $html .= '<td colspan="7" align="right" style="font-weight: bold; background-color: #fee2e2; color: #b91c1c;">TOTAL PENGELUARAN</td>';
            $html .= '<td align="right" style="font-weight: bold; background-color: #fee2e2; color: #b91c1c;">Rp '.number_format($totalPengeluaran, 0, ',', '.').'</td>';
            $html .= '</tr>';
        }

        // Saldo
        if (!$request->filled('type')) {
            $html .= '<tr>';
            $html .= '<td colspan="7" align="right" style="font-weight: bold; background-color: #e0e7ff; color: #4338ca; font-size: 14px;">SALDO AKHIR</td>';
            $html .= '<td align="right" style="font-weight: bold; background-color: #e0e7ff; color: #4338ca; font-size: 14px;">Rp '.number_format($totalPemasukan - $totalPengeluaran, 0, ',', '.').'</td>';
            $html .= '</tr>';
        }

        $html .= '</tfoot></table></body></html>';

        return response($html)
            ->header('Content-Type', 'application/vnd.ms-excel; charset=utf-8')
            ->header('Content-Disposition', 'attachment; filename='.$fileName);
    }
}
