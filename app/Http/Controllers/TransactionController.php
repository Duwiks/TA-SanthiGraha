<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\TransactionRejection;
use App\Models\Category;
use App\Models\Project;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::query()->with(['user:id,name', 'project:id,project_name', 'category:id,category_name', 'approver:id,name']);

        if (auth()->user()->role === 'admin') {
            // Lock to Approved Transactions Only for Admin's general ledger
            $query->where('status', 'approved');
        } else {
            // Pegawai sees ALL their own transactions regardless of status
            $query->where('user_id', auth()->id());
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function (\Illuminate\Database\Eloquent\Builder $q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhereHas(
                        'category',
                        function ($qCat) use ($search) {
                            $qCat->where('category_name', 'like', "%{$search}%");
                        }
                    )
                    ->orWhereHas(
                        'project',
                        function ($qProj) use ($search) {
                            $qProj->where('project_name', 'like', "%{$search}%");
                        }
                    );
            });
        }

        if ($request->filled('status') && auth()->user()->role === 'pegawai') {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by year
        if ($request->filled('year')) {
            $query->whereYear('transaction_date', $request->year);
        }

        // Calculate overall totals after applying all filters (search, status, type, year)
        $summaryQuery = clone $query;
        $totalPemasukan = (clone $summaryQuery)->where('type', 'pemasukan')->where('status', 'approved')->sum('amount');
        $totalPengeluaran = (clone $summaryQuery)->where('type', 'pengeluaran')->where('status', 'approved')->sum('amount');
        $saldo = $totalPemasukan - $totalPengeluaran;

        // Get distinct years available in the database for the dropdown
        if (auth()->user()->role === 'admin') {
            $availableYears = Transaction::where('status', 'approved')
                ->selectRaw('YEAR(transaction_date) as year')
                ->distinct()
                ->orderByRaw('YEAR(transaction_date) DESC')
                ->pluck('year')
                ->filter()
                ->values();
        } else {
            $availableYears = Transaction::where('user_id', auth()->id())
                ->selectRaw('YEAR(transaction_date) as year')
                ->distinct()
                ->orderByRaw('YEAR(transaction_date) DESC')
                ->pluck('year')
                ->filter()
                ->values();
        }

        $sort = $request->get('sort', 'latest');
        if ($sort === 'oldest') {
            $query->orderBy('transaction_date', 'asc')->orderBy('id', 'asc');
        } elseif ($sort === 'newest_input') {
            // Admin: urutkan berdasarkan waktu disetujui/diproses (updated_at)
            // Pegawai: urutkan berdasarkan waktu diajukan (created_at)
            $sortColumn = auth()->user()->role === 'admin' ? 'updated_at' : 'created_at';
            $query->orderBy($sortColumn, 'desc')->orderBy('id', 'desc');
        } else {
            $query->orderBy('transaction_date', 'desc')->orderBy('id', 'desc');
        }
        $transactions = $query->paginate(10)->withQueryString();

        if (auth()->user()->role === 'admin') {
            return view('admin.transaksi', compact('transactions', 'totalPemasukan', 'totalPengeluaran', 'saldo', 'availableYears'));
        } else {
            return view('pegawai.transaksi', compact('transactions', 'totalPemasukan', 'totalPengeluaran', 'saldo', 'availableYears'));
        }
    }

    public function approvals(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        // Antrean pending (menunggu aksi admin)
        $sort = $request->get('sort') === 'oldest' ? 'asc' : 'desc';
        $transactions = Transaction::query()
            ->where('status', 'pending')
            ->with(['user:id,name', 'project:id,project_name', 'category:id,category_name'])
            ->orderBy('created_at', $sort)
            ->orderBy('id', $sort)
            ->paginate(10)->withQueryString();

        // Riwayat: transaksi yang sudah diproses, dengan filter opsional
        $historyQuery = Transaction::query()
            ->with(['user:id,name', 'project:id,project_name', 'category:id,category_name', 'approver:id,name', 'rejections']);

        if ($request->filled('filter_status') && in_array($request->filter_status, ['approved', 'rejected'])) {
            $historyQuery->where('status', $request->filter_status);
        } else {
            $historyQuery->whereIn('status', ['approved', 'rejected']);
        }

        $history = $historyQuery->orderBy('updated_at', $sort)
            ->orderBy('id', $sort)
            ->paginate(12, ['*'], 'history_page')
            ->withQueryString();

        $historyApprovedCount = Transaction::where('status', 'approved')->count();
        $historyRejectedCount = Transaction::where('status', 'rejected')->count();

        return view('admin.approvals', compact(
            'transactions',
            'history',
            'historyApprovedCount',
            'historyRejectedCount'
        ));
    }

    public function create()
    {
        $categories = Category::all();
        $projects = Project::active()->get();
        return view('transactions.form', compact('categories', 'projects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'category_id' => 'required|exists:categories,id',
            'transaction_date' => 'required|date|before_or_equal:today',
            'type' => auth()->user()->role === 'admin' ? 'required|in:pemasukan,pengeluaran' : 'required|in:pengeluaran',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|gt:0',
            'payment_method' => 'required|string|max:100',
            'receipt_photo' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:15360',
        ], [
            'project_id.required' => 'Proyek wajib dipilih.',
            'project_id.exists' => 'Proyek tidak valid.',

            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori tidak valid.',

            'transaction_date.required' => 'Tanggal nota wajib diisi.',
            'transaction_date.date' => 'Format tanggal tidak valid.',
            'transaction_date.before_or_equal' => 'Tanggal nota tidak boleh melebihi hari ini.',

            'type.required' => 'Tipe transaksi wajib dipilih.',
            'type.in' => 'Tipe transaksi tidak valid.',

            'amount.required' => 'Nominal wajib diisi.',
            'amount.numeric' => 'Nominal harus berupa angka.',
            'amount.gt' => 'Nominal harus lebih besar dari Rp 0.',

            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'payment_method.max' => 'Metode pembayaran maksimal 100 karakter.',

            'receipt_photo.file' => 'File bukti transaksi tidak valid.',
            'receipt_photo.mimes' => 'Bukti transaksi harus berupa JPG, PNG, atau PDF.',
            'receipt_photo.max' => 'Ukuran file terlalu besar (maks. 15 MB). Gambar >5MB akan dikompres otomatis.',
        ]);

        $receiptPath = null;
        if ($request->hasFile('receipt_photo')) {
            $receiptPath = $this->compressAndSave($request->file('receipt_photo'), 'receipts');
        }

        Transaction::create([
            'user_id' => auth()->id(),
            'project_id' => $request->project_id,
            'category_id' => $request->category_id,
            'transaction_date' => $request->transaction_date,
            'type' => $request->type,
            'description' => $request->description,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'receipt_photo' => $receiptPath,
            'status' => auth()->user()->role === 'admin' ? 'approved' : 'pending',
            'approved_by' => auth()->user()->role === 'admin' ? auth()->id() : null,
        ]);

        $message = auth()->user()->role === 'admin'
            ? 'Transaksi berhasil ditambahkan!'
            : 'Transaksi berhasil diajukan, menunggu persetujuan Admin!';

        return redirect()->route('transactions.index')->with('success', $message);
    }

    public function edit($id)
    {
        $transaction = Transaction::findOrFail($id);

        if (auth()->user()->role === 'pegawai' && $transaction->user_id !== auth()->id()) {
            abort(403);
        }

        if (auth()->user()->role === 'pegawai' && !in_array($transaction->status, ['pending', 'rejected'])) {
            return redirect()->route('transactions.index')->with('error', 'Transaksi yang sudah disetujui tidak dapat diedit.');
        }

        $transaction->load('rejections');
        $categories = Category::all();
        $projects = Project::all();
        return view('transactions.form', compact('transaction', 'categories', 'projects'));
    }

    public function update(Request $request, $id)
    {
        $transaction = Transaction::findOrFail($id);

        if (auth()->user()->role === 'pegawai' && $transaction->user_id !== auth()->id()) {
            abort(403);
        }

        if (auth()->user()->role === 'pegawai' && !in_array($transaction->status, ['pending', 'rejected'])) {
            return redirect()->route('transactions.index')->with('error', 'Transaksi yang sudah disetujui tidak dapat diedit.');
        }

        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'category_id' => 'required|exists:categories,id',
            'transaction_date' => 'required|date|before_or_equal:today',
            'type' => auth()->user()->role === 'admin' ? 'required|in:pemasukan,pengeluaran' : 'required|in:pengeluaran',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|gt:0',
            'payment_method' => 'required|string|max:100',
            'receipt_photo' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:15360',
        ], [
            'project_id.required' => 'Proyek wajib dipilih.',
            'project_id.exists' => 'Proyek tidak valid.',

            'category_id.required' => 'Kategori wajib dipilih.',
            'category_id.exists' => 'Kategori tidak valid.',

            'transaction_date.required' => 'Tanggal nota wajib diisi.',
            'transaction_date.date' => 'Format tanggal tidak valid.',
            'transaction_date.before_or_equal' => 'Tanggal nota tidak boleh melebihi hari ini.',

            'type.required' => 'Tipe transaksi wajib dipilih.',
            'type.in' => 'Tipe transaksi tidak valid.',

            'amount.required' => 'Nominal wajib diisi.',
            'amount.numeric' => 'Nominal harus berupa angka.',
            'amount.gt' => 'Nominal harus lebih besar dari Rp 0.',

            'payment_method.required' => 'Metode pembayaran wajib dipilih.',
            'payment_method.max' => 'Metode pembayaran maksimal 100 karakter.',

            'receipt_photo.file' => 'File bukti transaksi tidak valid.',
            'receipt_photo.mimes' => 'Bukti transaksi harus berupa JPG, PNG, atau PDF.',
            'receipt_photo.max' => 'Ukuran file terlalu besar (maks. 15 MB). Gambar >5MB akan dikompres otomatis.',
        ]);

        $data = [
            'project_id' => $request->project_id,
            'category_id' => $request->category_id,
            'transaction_date' => $request->transaction_date,
            'type' => $request->type,
            'description' => $request->description,
            'amount' => $request->amount,
            'payment_method' => $request->payment_method,
            'status' => auth()->user()->role === 'admin' ? 'approved' : 'pending',
            'approved_by' => auth()->user()->role === 'admin' ? auth()->id() : null,
        ];

        if ($request->hasFile('receipt_photo')) {
            if ($transaction->receipt_photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($transaction->receipt_photo)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($transaction->receipt_photo);
            }
            $data['receipt_photo'] = $this->compressAndSave($request->file('receipt_photo'), 'receipts');
        }

        $transaction->update($data);

        $message = auth()->user()->role === 'admin'
            ? 'Transaksi berhasil diperbarui!'
            : 'Transaksi berhasil diperbarui, menunggu persetujuan Admin!';

        return redirect()->route('transactions.index')->with('success', $message);
    }

    public function destroy($id)
    {
        $transaction = Transaction::findOrFail($id);

        if (auth()->user()->role === 'pegawai' && $transaction->user_id !== auth()->id()) {
            abort(403);
        }

        if ($transaction->status === 'approved' && auth()->user()->role !== 'admin') {
            return redirect()->route('transactions.index')->with('error', 'Transaksi yang sudah disetujui tidak dapat dihapus.');
        }

        // Sinkronisasi status Nota Merah jika transaksi ini berasal dari Nota Merah
        // Dibungkus DB::transaction() agar operasi bersifat atomic
        \Illuminate\Support\Facades\DB::transaction(function () use ($transaction) {
            if ($transaction->nota_merah_id) {
                $nota = \App\Models\NotaMerah::find($transaction->nota_merah_id);
                if ($nota) {
                    $nota->update([
                        'status'       => 'menunggu_verifikasi',
                        'confirmed_at' => null,
                    ]);
                }
            }

            // Jangan hapus file bukti dari storage jika berasal dari Nota Merah, karena file tersebut masih digunakan
            if (!$transaction->nota_merah_id && $transaction->receipt_photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($transaction->receipt_photo)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($transaction->receipt_photo);
            }

            $transaction->delete();
        });

        return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil dihapus');
    }

    public function approve(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin')
            abort(403);

        $transaction = Transaction::findOrFail($id);

        if ($transaction->status !== 'pending') {
            return back()->with('error', 'Transaksi ini sudah diproses.');
        }

        $transaction->update([
            'status' => 'approved',
            'approved_by' => auth()->id()
        ]);

        return back()->with('success', 'Transaksi disetujui!');
    }

    public function reject(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin')
            abort(403);

        $transaction = Transaction::findOrFail($id);

        if ($transaction->status !== 'pending') {
            return back()->with('error', 'Transaksi ini sudah diproses.');
        }

        $request->validate([
            'reason' => 'required|string|max:255'
        ]);

        $transaction->update([
            'status' => 'rejected'
        ]);

        TransactionRejection::create([
            'transaction_id' => $transaction->id,
            'reason' => $request->reason,
            'created_at' => now(),
        ]);

        return back()->with('success', 'Transaksi ditolak dan dicatat');
    }

    // ---------------------------------------------------------------
    // HELPER – Kompres dan simpan file gambar/PDF
    // Gambar: adaptive quality loop (GD). PDF: Imagick (fallback store langsung).
    // Menjamin output gambar < 5 MB.
    // ---------------------------------------------------------------
    private function compressAndSave($file, string $folder): string
    {
        $ext = strtolower($file->extension());
        $dir = storage_path("app/public/{$folder}");

        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }

        // ── PDF ──────────────────────────────────────────────────────
        if ($ext === 'pdf') {
            if (extension_loaded('imagick') && $file->getSize() > 5 * 1024 * 1024) {
                try {
                    $imagick = new \Imagick();
                    $imagick->setResolution(100, 100);
                    $imagick->readImage($file->getRealPath());
                    $imagick->setImageFormat('pdf');
                    $imagick->setCompressionQuality(60);
                    $outPath = $dir . '/' . uniqid() . '_compressed.pdf';
                    $imagick->writeImages($outPath, true);
                    $imagick->clear();
                    $imagick->destroy();
                    // Jika hasil kompresi lebih besar dari aslinya, pakai file asli
                    if (file_exists($outPath) && filesize($outPath) < $file->getSize()) {
                        return "{$folder}/" . basename($outPath);
                    }
                    if (file_exists($outPath)) {
                        @unlink($outPath);
                    }
                } catch (\Exception $e) {
                    // fallback ke store langsung
                }
            }
            return $file->store($folder, 'public');
        }

        // ── GAMBAR (JPG / PNG) ────────────────────────────────────────
        if (!in_array($ext, ['jpg', 'jpeg', 'png'])) {
            return $file->store($folder, 'public');
        }

        // Jika gambar kecil (<= 1MB), simpan langsung tanpa kompresi
        if ($file->getSize() <= 1024 * 1024) {
            return $file->store($folder, 'public');
        }

        $sourceImage = $ext === 'png'
            ? @imagecreatefrompng($file->getRealPath())
            : @imagecreatefromjpeg($file->getRealPath());

        if ($sourceImage === false) {
            return $file->store($folder, 'public');
        }

        $maxBytes = 5 * 1024 * 1024; // 5 MB
        $outPath = $dir . '/' . uniqid() . '_compressed.jpg';
        $compressed = false;

        // Adaptive quality loop: mulai 85, turun 15 per langkah
        foreach ([85, 70, 55, 40, 25, 10] as $quality) {
            imagejpeg($sourceImage, $outPath, $quality);
            if (filesize($outPath) <= $maxBytes) {
                $compressed = true;
                break;
            }
        }

        // Jika masih > 5MB setelah loop → resize 50% lalu kompres ulang
        if (!$compressed || filesize($outPath) > $maxBytes) {
            $w = imagesx($sourceImage);
            $h = imagesy($sourceImage);
            $resized = imagecreatetruecolor((int) ($w / 2), (int) ($h / 2));
            imagecopyresampled($resized, $sourceImage, 0, 0, 0, 0, (int) ($w / 2), (int) ($h / 2), $w, $h);
            imagejpeg($resized, $outPath, 60);
            imagedestroy($resized);
        }

        imagedestroy($sourceImage);
        return "{$folder}/" . basename($outPath);
    }
}
