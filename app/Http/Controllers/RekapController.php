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
}
