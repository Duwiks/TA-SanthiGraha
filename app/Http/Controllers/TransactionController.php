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

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status') && auth()->user()->role === 'pegawai') {
            $query->where('status', $request->status);
        }

        $transactions = $query->orderBy('transaction_date', 'desc')->paginate(10)->withQueryString();

        $summaryQuery = clone $query;
        $totalPemasukan = (clone $summaryQuery)->where('type', 'pemasukan')->where('status', 'approved')->sum('amount');
        $totalPengeluaran = (clone $summaryQuery)->where('type', 'pengeluaran')->where('status', 'approved')->sum('amount');
        $saldo = $totalPemasukan - $totalPengeluaran;

        if (auth()->user()->role === 'admin') {
            return view('admin.transaksi', compact('transactions', 'totalPemasukan', 'totalPengeluaran', 'saldo'));
        } else {
            return view('pegawai.transaksi', compact('transactions', 'totalPemasukan', 'totalPengeluaran', 'saldo'));
        }
    }

    public function approvals(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $query = Transaction::query()->where('status', 'pending')->with(['user:id,name', 'project:id,project_name', 'category:id,category_name']);
        $transactions = $query->orderBy('transaction_date', 'asc')->paginate(10);

        return view('admin.approvals', compact('transactions'));
    }

    public function create()
    {
        $categories = Category::all();
        $projects = Project::all();
        return view('transactions.form', compact('categories', 'projects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'category_id' => 'required|exists:categories,id',
            'transaction_date' => 'required|date',
            'type' => 'required|in:pemasukan,pengeluaran',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:Cash,Bank BPD,BRI,BCA',
            'receipt_photo' => 'required|file|mimes:jpeg,png,jpg,pdf|max:20480',
        ], [
            'project_id.required' => 'Proyek wajib dipilih.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'transaction_date.required' => 'Tanggal transaksi wajib diisi.',
            'type.required' => 'Tipe transaksi wajib dipilih.',
            'amount.required' => 'Nominal wajib diisi.',
            'payment_method.required' => 'Metode transfer wajib dipilih.',
            'receipt_photo.required' => 'Bukti transaksi wajib dilampirkan.',
            'receipt_photo.mimes' => 'Format foto harus berupa jpeg, png, jpg, atau pdf.',
            'receipt_photo.max' => 'Ukuran asli foto gagal diunggah ke server (melewati batas maksimal 20MB).',
        ]);

        $receiptPath = null;
        if ($request->hasFile('receipt_photo')) {
            $file = $request->file('receipt_photo');

            // Server-Side Compression untuk Gambar di atas 1MB
            if (in_array(strtolower($file->extension()), ['jpg', 'jpeg', 'png']) && $file->getSize() > 1024 * 1024) {
                $sourceImage = null;
                if (strtolower($file->extension()) == 'png') {
                    $sourceImage = @imagecreatefrompng($file->getRealPath());
                } else {
                    $sourceImage = @imagecreatefromjpeg($file->getRealPath());
                }

                if ($sourceImage !== false) {
                    $path = storage_path('app/public/receipts/' . uniqid() . '_compressed.jpg');
                    if (!file_exists(dirname($path))) {
                        mkdir(dirname($path), 0755, true);
                    }
                    imagejpeg($sourceImage, $path, 60); // Pengecilan ekstrem kualitas 60% agar hemat memori (dibawah 5MB)
                    imagedestroy($sourceImage);
                    $receiptPath = 'receipts/' . basename($path);
                } else {
                    $receiptPath = $file->store('receipts', 'public');
                }
            } else {
                $receiptPath = $file->store('receipts', 'public');
            }
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

        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'category_id' => 'required|exists:categories,id',
            'transaction_date' => 'required|date',
            'type' => 'required|in:pemasukan,pengeluaran',
            'description' => 'nullable|string',
            'amount' => 'required|numeric|min:0',
            'payment_method' => 'required|in:Cash,Bank BPD,BRI,BCA',
            'receipt_photo' => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:20480',
        ], [
            'project_id.required' => 'Proyek wajib dipilih.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'transaction_date.required' => 'Tanggal transaksi wajib diisi.',
            'type.required' => 'Tipe transaksi wajib dipilih.',
            'amount.required' => 'Nominal wajib diisi.',
            'payment_method.required' => 'Metode transfer wajib dipilih.',
            'receipt_photo.mimes' => 'Format foto harus berupa jpeg, png, jpg, atau pdf.',
            'receipt_photo.max' => 'Ukuran asli foto gagal diunggah ke server (melewati batas maksimal 20MB).',
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

            $file = $request->file('receipt_photo');

            // Server-Side Compression untuk Gambar di atas 1MB
            if (in_array(strtolower($file->extension()), ['jpg', 'jpeg', 'png']) && $file->getSize() > 1024 * 1024) {
                $sourceImage = null;
                if (strtolower($file->extension()) == 'png') {
                    $sourceImage = @imagecreatefrompng($file->getRealPath());
                } else {
                    $sourceImage = @imagecreatefromjpeg($file->getRealPath());
                }

                if ($sourceImage !== false) {
                    $path = storage_path('app/public/receipts/' . uniqid() . '_compressed.jpg');
                    if (!file_exists(dirname($path))) {
                        mkdir(dirname($path), 0755, true);
                    }
                    imagejpeg($sourceImage, $path, 60);
                    imagedestroy($sourceImage);
                    $data['receipt_photo'] = 'receipts/' . basename($path);
                } else {
                    $data['receipt_photo'] = $file->store('receipts', 'public');
                }
            } else {
                $data['receipt_photo'] = $file->store('receipts', 'public');
            }
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

        if ($transaction->receipt_photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($transaction->receipt_photo)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($transaction->receipt_photo);
        }
        $transaction->delete();
        return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil dihapus');
    }

    public function approve(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin')
            abort(403);

        $transaction = Transaction::findOrFail($id);

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
}
