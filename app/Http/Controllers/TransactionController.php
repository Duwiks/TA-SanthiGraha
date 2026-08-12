<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\Transaction;
use App\Models\TransactionRejection;
use App\Models\Category;
use App\Models\Project;
use App\Models\PaymentGroup;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Transaction::query()->with([
            'user:id,name',
            'project:id,project_name',
            'category:id,category_name',
            'approver:id,name',
            'paymentGroup',
        ]);

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

        if ($request->filled('payment_stage')) {
            $query->where('payment_stage', $request->payment_stage);
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

        // Ambil seluruh data hasil filter untuk dikonsolidasikan per Payment Group / Proyek + Kategori
        $rawTransactions = $query->get();

        // Kelompokkan transaksi yang memiliki kelompok / kombinasi proyek + kategori yang sama
        $consolidated = collect();
        $processedKeys = [];

        foreach ($rawTransactions as $trx) {
            // JIKA STATUS BUKAN APPROVED (misal pending atau rejected) -> Jangan kelompokkan! Tampilkan mandiri
            if ($trx->status !== 'approved') {
                $trx->is_grouped = false;
                $trx->group_receipts_count = 1;
                $trx->group_total_amount = $trx->amount;
                $consolidated->push($trx);
                continue;
            }

            $groupKey = $trx->payment_group_id
                ? 'pg_' . $trx->payment_group_id
                : 'pc_' . $trx->project_id . '_' . $trx->category_id . '_' . $trx->type;

            if (in_array($groupKey, $processedKeys)) {
                continue;
            }
            $processedKeys[] = $groupKey;

            // Ambil semua transaksi APPROVED dalam kelompok ini
            if ($trx->payment_group_id) {
                $groupMembers = $rawTransactions->where('status', 'approved')->where('payment_group_id', $trx->payment_group_id);
            } else {
                $groupMembers = $rawTransactions->where('status', 'approved')
                    ->where('project_id', $trx->project_id)
                    ->where('category_id', $trx->category_id)
                    ->where('type', $trx->type);
            }

            if ($groupMembers->count() > 1 || $trx->payment_group_id) {
                // Representasi baris kelompok
                $rep = clone $trx;
                $rep->is_grouped = $groupMembers->count() > 1;
                $rep->group_id = $trx->payment_group_id;
                $rep->group_receipts_count = $groupMembers->count();
                $rep->group_total_amount = $groupMembers->sum('amount');
                $rep->amount = $groupMembers->sum('amount'); // Nominal akumulasi seluruh nota
                $rep->group_transactions = $groupMembers;
                $rep->group_receipt_photos = $groupMembers->pluck('receipt_photo')->filter()->values();
                $rep->payment_stage = $trx->paymentGroup ? $trx->paymentGroup->payment_status : $trx->payment_stage;

                $consolidated->push($rep);
            } else {
                // Transaksi mandiri (1 nota)
                $trx->is_grouped = false;
                $trx->group_receipts_count = 1;
                $trx->group_total_amount = $trx->amount;
                $consolidated->push($trx);
            }
        }

        // Pagination untuk koleksi konsolidasi
        $page = (int) $request->get('page', 1);
        $perPage = 10;
        $slicedItems = $consolidated->slice(($page - 1) * $perPage, $perPage)->values();

        $transactions = new LengthAwarePaginator(
            $slicedItems,
            $consolidated->count(),
            $perPage,
            $page,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

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

    public function adminShow(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin')
            abort(403);

        $transaction = Transaction::with([
            'user',
            'project',
            'category',
            'approver',
            'notaMerah',
            'paymentGroup',
        ])->findOrFail($id);

        $paymentGroup = null;
        $groupTransactions = collect([$transaction]);
        $totalGroupAmount = $transaction->amount;

        // Hanya kelompokkan jika transaksi sudah approved dan punya payment_group_id
        if ($transaction->status === 'approved' && $transaction->payment_group_id) {
            $paymentGroup = PaymentGroup::with([
                'project',
                'category',
                'transactions' => function ($q) {
                    $q->where('status', 'approved')
                      ->with(['user:id,name', 'approver:id,name'])
                      ->orderByDesc('transaction_date')
                      ->orderByDesc('id');
                }
            ])->find($transaction->payment_group_id);

            if ($paymentGroup && $paymentGroup->transactions->count() > 0) {
                $groupTransactions = $paymentGroup->transactions;
                $totalGroupAmount = $groupTransactions->sum('amount');
            }
        }

        // 'from' menentukan tombol Kembali: 'approvals' atau 'transactions' (default)
        $from = $request->get('from', 'transactions');

        return view('admin.transaction-show', compact(
            'transaction',
            'paymentGroup',
            'groupTransactions',
            'totalGroupAmount',
            'from'
        ));
    }

    public function show($id)
    {
        $transaction = Transaction::with([
            'user',
            'project',
            'category',
            'approver',
            'notaMerah',
            'paymentGroup',
        ])->findOrFail($id);

        // Pegawai hanya boleh melihat transaksi miliknya sendiri
        if (auth()->user()->role === 'pegawai' && $transaction->user_id !== auth()->id()) {
            abort(403);
        }

        $paymentGroup = null;
        $groupTransactions = collect([$transaction]);
        $totalGroupAmount = $transaction->amount;

        // Hanya tampilkan grup jika transaksi sudah approved dan punya payment_group_id
        if ($transaction->status === 'approved' && $transaction->payment_group_id) {
            $paymentGroup = PaymentGroup::with([
                'project',
                'category',
                'transactions' => function ($q) {
                    $q->where('user_id', auth()->id())
                      ->where('status', 'approved')
                      ->with(['user:id,name', 'approver:id,name'])
                      ->orderByDesc('transaction_date')
                      ->orderByDesc('id');
                }
            ])->find($transaction->payment_group_id);

            if ($paymentGroup && $paymentGroup->transactions->count() > 0) {
                $groupTransactions = $paymentGroup->transactions;
                $totalGroupAmount = $groupTransactions->sum('amount');
            }
        }

        return view('pegawai.transaction-show', compact(
            'transaction',
            'paymentGroup',
            'groupTransactions',
            'totalGroupAmount'
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
        $isAdmin = auth()->user()->role === 'admin';
        $type = $isAdmin ? ($request->type ?? 'pengeluaran') : 'pengeluaran';

        $existingGroup = PaymentGroup::where('project_id', $request->project_id)
            ->where('category_id', $request->category_id)
            ->where('type', $type)
            ->orderByDesc('id')
            ->first();
        $isExistingCompleted = $existingGroup && $existingGroup->payment_status === 'selesai';
        $hasActiveGroup      = $existingGroup && $existingGroup->payment_status !== 'selesai';

        if ($isAdmin && $isExistingCompleted) {
            $action = $request->payment_group_action ?: 'lanjutkan';
            $request->merge(['payment_group_action' => $action]);
        }

        if ($isAdmin) {
            if ($request->payment_group_action === 'baru') {
                $stageRule = 'required|in:uang_muka,selesai';
            } elseif ($request->payment_group_action === 'lanjutkan' || $hasActiveGroup) {
                // Safety net: izinkan uang_muka juga (akan dinormalisasi ke proses di bawah)
                $stageRule = 'required|in:uang_muka,proses,selesai';
            } else {
                $stageRule = 'required|in:uang_muka,proses,selesai';
            }
        } elseif ($isExistingCompleted) {
            $stageRule = 'nullable|in:uang_muka,proses';
        } elseif ($hasActiveGroup) {
            // Safety net: izinkan uang_muka juga (akan dinormalisasi ke proses di bawah)
            $stageRule = 'required|in:uang_muka,proses';
        } else {
            $stageRule = 'required|in:uang_muka,selesai';
        }

        $actionRule = ($isAdmin && $isExistingCompleted) ? 'required|in:lanjutkan,baru' : 'nullable|in:lanjutkan,baru';

        $request->validate([
            'project_id'           => 'required|exists:projects,id',
            'category_id'          => 'required|exists:categories,id',
            'transaction_date'     => 'required|date|before_or_equal:today',
            'type'                 => $isAdmin ? 'required|in:pemasukan,pengeluaran' : 'required|in:pengeluaran',
            'description'          => 'nullable|string',
            'amount'               => 'required|numeric|gt:0',
            'payment_method'       => 'required|string|max:100',
            'receipt_photo'        => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:15360',
            'payment_stage'        => $stageRule,
            'payment_group_action' => $actionRule,
            'payment_group_label'  => 'required_if:payment_group_action,baru|nullable|string|max:255',
        ], [
            'payment_group_action.required'   => 'Kombinasi proyek dan kategori ini sebelumnya sudah selesai. Silakan tentukan apakah transaksi ini melanjutkan kelompok lama atau membuat kelompok baru pada pop-up konfirmasi.',
            'project_id.required'             => 'Proyek wajib dipilih.',
            'project_id.exists'               => 'Proyek tidak valid.',
            'category_id.required'            => 'Kategori wajib dipilih.',
            'category_id.exists'              => 'Kategori tidak valid.',
            'transaction_date.required'       => 'Tanggal nota wajib diisi.',
            'transaction_date.date'           => 'Format tanggal tidak valid.',
            'transaction_date.before_or_equal' => 'Tanggal nota tidak boleh melebihi hari ini.',
            'type.required'                   => 'Tipe transaksi wajib dipilih.',
            'type.in'                         => 'Tipe transaksi tidak valid.',
            'amount.required'                 => 'Nominal wajib diisi.',
            'amount.numeric'                  => 'Nominal harus berupa angka.',
            'amount.gt'                       => 'Nominal harus lebih besar dari Rp 0.',
            'payment_method.required'         => 'Metode pembayaran wajib dipilih.',
            'payment_method.max'              => 'Metode pembayaran maksimal 100 karakter.',
            'receipt_photo.file'              => 'File bukti transaksi tidak valid.',
            'receipt_photo.mimes'             => 'Bukti transaksi harus berupa JPG, PNG, atau PDF.',
            'receipt_photo.max'               => 'Ukuran file terlalu besar (maks. 15 MB). Gambar >5MB akan dikompres otomatis.',
            'payment_stage.required'          => 'Status pembayaran wajib dipilih.',
            'payment_stage.in'                => 'Status pembayaran tidak valid.',
            'payment_group_label.required_if' => 'Label Payment Group wajib diisi saat membuat kelompok pembayaran baru.',
        ]);

        $receiptPath = null;
        if ($request->hasFile('receipt_photo')) {
            $receiptPath = $this->compressAndSave($request->file('receipt_photo'), 'receipts');
        }

        // Deteksi / buat Payment Group HANYA jika admin
        $paymentGroupId = null;
        if ($isAdmin) {
            $action = $request->payment_group_action;
            $label  = $request->payment_group_label;
            $paymentGroupId = $this->resolvePaymentGroup(
                $request->project_id,
                $request->category_id,
                $request->type,
                $action,
                $label
            );
        }

        $stage = $request->payment_stage;
        if (!$isAdmin && $isExistingCompleted) {
            // Pegawai pada kelompok yang sudah selesai -> status null sampai divalidasi admin
            $stage = null;
        } elseif ($hasActiveGroup && $stage === 'uang_muka' && (!$isAdmin || $request->payment_group_action !== 'baru')) {
            // Safety net: normalisasi uang_muka → proses jika grup aktif berjalan & bukan aksi baru
            $stage = 'proses';
        } elseif (!$isAdmin && $hasActiveGroup) {
            // Pegawai pada kelompok aktif → selalu proses
            $stage = 'proses';
        }

        $transaction = Transaction::create([
            'user_id'          => auth()->id(),
            'project_id'       => $request->project_id,
            'category_id'      => $request->category_id,
            'transaction_date' => $request->transaction_date,
            'type'             => $request->type,
            'description'      => $request->description,
            'amount'           => $request->amount,
            'payment_method'   => $request->payment_method,
            'receipt_photo'    => $receiptPath,
            'status'           => $isAdmin ? 'approved' : 'pending',
            'approved_by'      => $isAdmin ? auth()->id() : null,
            'payment_group_id' => $paymentGroupId,
            'payment_stage'    => $stage,
        ]);

        // Transaksi admin langsung approved → update Payment Group cache seketika
        if ($isAdmin && $paymentGroupId) {
            PaymentGroup::find($paymentGroupId)?->syncStatus();
        }

        $message = $isAdmin
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
        $projects = Project::active()->get();

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

        $isAdmin = auth()->user()->role === 'admin';
        $type = $isAdmin ? ($request->type ?? 'pengeluaran') : 'pengeluaran';

        $existingGroup = PaymentGroup::where('project_id', $request->project_id)
            ->where('category_id', $request->category_id)
            ->where('type', $type)
            ->orderByDesc('id')
            ->first();
        $isExistingCompleted = $existingGroup && $existingGroup->payment_status === 'selesai';
        $hasActiveGroup      = $existingGroup && $existingGroup->payment_status !== 'selesai';

        if ($isAdmin && $isExistingCompleted) {
            $action = $request->payment_group_action ?: 'lanjutkan';
            $request->merge(['payment_group_action' => $action]);
        }

        if ($isAdmin) {
            if ($request->payment_group_action === 'baru') {
                $stageRule = 'required|in:uang_muka,selesai';
            } elseif ($request->payment_group_action === 'lanjutkan' || $hasActiveGroup) {
                // Safety net: izinkan uang_muka juga (akan dinormalisasi ke proses di bawah)
                $stageRule = 'required|in:uang_muka,proses,selesai';
            } else {
                $stageRule = 'required|in:uang_muka,proses,selesai';
            }
        } elseif ($isExistingCompleted) {
            $stageRule = 'nullable|in:uang_muka,proses';
        } elseif ($hasActiveGroup) {
            // Safety net: izinkan uang_muka juga (akan dinormalisasi ke proses di bawah)
            $stageRule = 'required|in:uang_muka,proses';
        } else {
            $stageRule = 'required|in:uang_muka,selesai';
        }

        $actionRule = ($isAdmin && $isExistingCompleted) ? 'required|in:lanjutkan,baru' : 'nullable|in:lanjutkan,baru';

        $request->validate([
            'project_id'           => 'required|exists:projects,id',
            'category_id'          => 'required|exists:categories,id',
            'transaction_date'     => 'required|date|before_or_equal:today',
            'type'                 => $isAdmin ? 'required|in:pemasukan,pengeluaran' : 'required|in:pengeluaran',
            'description'          => 'nullable|string',
            'amount'               => 'required|numeric|gt:0',
            'payment_method'       => 'required|string|max:100',
            'receipt_photo'        => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:15360',
            'payment_stage'        => $stageRule,
            'payment_group_action' => $actionRule,
            'payment_group_label'  => 'required_if:payment_group_action,baru|nullable|string|max:255',
        ], [
            'payment_group_action.required'   => 'Kombinasi proyek dan kategori ini sebelumnya sudah selesai. Silakan tentukan apakah transaksi ini melanjutkan kelompok lama atau membuat kelompok baru pada pop-up konfirmasi.',
            'project_id.required'             => 'Proyek wajib dipilih.',
            'project_id.exists'               => 'Proyek tidak valid.',
            'category_id.required'            => 'Kategori wajib dipilih.',
            'category_id.exists'              => 'Kategori tidak valid.',
            'transaction_date.required'       => 'Tanggal nota wajib diisi.',
            'transaction_date.date'           => 'Format tanggal tidak valid.',
            'transaction_date.before_or_equal' => 'Tanggal nota tidak boleh melebihi hari ini.',
            'type.required'                   => 'Tipe transaksi wajib dipilih.',
            'type.in'                         => 'Tipe transaksi tidak valid.',
            'amount.required'                 => 'Nominal wajib diisi.',
            'amount.numeric'                  => 'Nominal harus berupa angka.',
            'amount.gt'                       => 'Nominal harus lebih besar dari Rp 0.',
            'payment_method.required'         => 'Metode pembayaran wajib dipilih.',
            'payment_method.max'              => 'Metode pembayaran maksimal 100 karakter.',
            'receipt_photo.file'              => 'File bukti transaksi tidak valid.',
            'receipt_photo.mimes'             => 'Bukti transaksi harus berupa JPG, PNG, atau PDF.',
            'receipt_photo.max'               => 'Ukuran file terlalu besar (maks. 15 MB). Gambar >5MB akan dikompres otomatis.',
            'payment_stage.required'          => 'Status pembayaran wajib dipilih.',
            'payment_stage.in'                => 'Status pembayaran tidak valid.',
            'payment_group_label.required_if' => 'Label Payment Group wajib diisi saat membuat kelompok pembayaran baru.',
        ]);

        $paymentGroupId = null;
        if ($isAdmin) {
            $projectChanged  = (int) $request->project_id  !== (int) $transaction->project_id;
            $categoryChanged = (int) $request->category_id !== (int) $transaction->category_id;
            $typeChanged     = $request->type !== $transaction->type;

            $paymentGroupId = $transaction->payment_group_id;
            if ($projectChanged || $categoryChanged || $typeChanged || !$transaction->payment_group_id) {
                if ($transaction->payment_group_id) {
                    PaymentGroup::find($transaction->payment_group_id)?->syncStatus();
                }
                $paymentGroupId = $this->resolvePaymentGroup(
                    $request->project_id,
                    $request->category_id,
                    $request->type,
                    $request->payment_group_action,
                    $request->payment_group_label
                );
            }
        }

        $stage = $request->payment_stage;
        if (!$isAdmin && $isExistingCompleted) {
            $stage = null;
        } elseif ($hasActiveGroup && $stage === 'uang_muka' && (!$isAdmin || $request->payment_group_action !== 'baru')) {
            // Safety net: normalisasi uang_muka → proses jika grup aktif berjalan
            $stage = 'proses';
        } elseif (!$isAdmin && $hasActiveGroup) {
            // Pegawai pada kelompok aktif → selalu proses
            $stage = 'proses';
        }

        $data = [
            'project_id'       => $request->project_id,
            'category_id'      => $request->category_id,
            'transaction_date' => $request->transaction_date,
            'type'             => $request->type,
            'description'      => $request->description,
            'amount'           => $request->amount,
            'payment_method'   => $request->payment_method,
            'status'           => $isAdmin ? 'approved' : 'pending',
            'approved_by'      => $isAdmin ? auth()->id() : null,
            'payment_group_id' => $paymentGroupId,
            'payment_stage'    => $stage,
        ];

        if ($request->hasFile('receipt_photo')) {
            if ($transaction->receipt_photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($transaction->receipt_photo)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($transaction->receipt_photo);
            }
            $data['receipt_photo'] = $this->compressAndSave($request->file('receipt_photo'), 'receipts');
        }

        $transaction->update($data);

        if ($isAdmin && $paymentGroupId) {
            PaymentGroup::find($paymentGroupId)?->syncStatus();
        }

        $message = $isAdmin
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
        $paymentGroupId = $transaction->payment_group_id;

        \Illuminate\Support\Facades\DB::transaction(function () use ($transaction) {
            if ($transaction->nota_merah_id) {
                $nota = \App\Models\NotaMerah::find($transaction->nota_merah_id);
                if ($nota) {
                    $nota->update([
                        'status' => 'menunggu_verifikasi',
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

        // Sinkronkan kembali status Payment Group setelah transaksi dihapus
        $remainingTrxInGroup = null;
        if ($paymentGroupId) {
            $group = PaymentGroup::find($paymentGroupId);
            if ($group) {
                if ($group->transactions()->count() === 0) {
                    $group->delete();
                } else {
                    $group->syncStatus();
                    $remainingTrxInGroup = $group->transactions()->orderByDesc('id')->first();
                }
            }
        }

        // Penanganan Redirect yang aman untuk mencegah 404 Not Found:
        $previousUrl = url()->previous();

        // 1. Jika request berasal dari halaman detail transaksi (adminShow)
        if (str_contains($previousUrl, 'transactions/admin-show') || str_contains($previousUrl, 'transactions/' . $id)) {
            if ($remainingTrxInGroup) {
                return redirect()->route('transactions.admin-show', $remainingTrxInGroup->id)
                    ->with('success', 'Nota transaksi berhasil dihapus.');
            }
            return redirect()->route('transactions.index')
                ->with('success', 'Transaksi berhasil dihapus.');
        }

        // 2. Jika berasal dari detail kelompok pembayaran dan kelompok telah terhapus (karena 0 transaksi)
        if ($paymentGroupId && !PaymentGroup::find($paymentGroupId) && str_contains($previousUrl, 'payment-groups/' . $paymentGroupId)) {
            return redirect()->route('payment-groups.index')
                ->with('success', 'Transaksi dan kelompok pembayaran berhasil dihapus.');
        }

        return back()->with('success', 'Transaksi berhasil dihapus.');
    }

    public function approve(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin')
            abort(403);

        $transaction = Transaction::findOrFail($id);

        if ($transaction->status !== 'pending') {
            return back()->with('error', 'Transaksi ini sudah diproses.');
        }

        $stageRule = 'nullable|in:uang_muka,proses,selesai';
        if ($request->payment_group_action === 'baru') {
            $stageRule = 'required|in:uang_muka,selesai';
        } elseif ($request->payment_group_action === 'lanjutkan') {
            $stageRule = 'required|in:proses,selesai';
        }

        $request->validate([
            'payment_stage'        => $stageRule,
            'payment_group_action' => 'nullable|in:lanjutkan,baru',
            'payment_group_label'  => 'required_if:payment_group_action,baru|nullable|string|max:255',
        ], [
            'payment_stage.required'          => 'Status pembayaran wajib dipilih.',
            'payment_stage.in'                => 'Status pembayaran tidak valid untuk pilihan kelompok.',
            'payment_group_label.required_if' => 'Label kelompok baru wajib diisi.',
        ]);

        $paymentGroupId = null;

        // Resolusi Payment Group saat Approval
        if ($request->payment_group_action === 'baru') {
            $paymentGroupId = $this->resolvePaymentGroup(
                $transaction->project_id,
                $transaction->category_id,
                $transaction->type,
                'baru',
                $request->payment_group_label
            );
        } elseif ($request->payment_group_action === 'lanjutkan') {
            $paymentGroupId = $this->resolvePaymentGroup(
                $transaction->project_id,
                $transaction->category_id,
                $transaction->type,
                'lanjutkan',
                null
            );
        } else {
            // Direct approve: cari kelompok aktif terbaru untuk proyek+kategori+tipe
            $latestGroup = PaymentGroup::where('project_id', $transaction->project_id)
                ->where('category_id', $transaction->category_id)
                ->where('type', $transaction->type)
                ->orderByDesc('id')
                ->first();

            if ($latestGroup) {
                // Gunakan latest group
                $paymentGroupId = $latestGroup->id;
            } else {
                // Buat PaymentGroup baru jika belum ada
                $paymentGroupId = $this->resolvePaymentGroup(
                    $transaction->project_id,
                    $transaction->category_id,
                    $transaction->type,
                    null,
                    null
                );
            }
        }

        $updateData = [
            'status'           => 'approved',
            'approved_by'      => auth()->id(),
            'payment_group_id' => $paymentGroupId,
        ];

        if ($request->filled('payment_stage')) {
            $updateData['payment_stage'] = $request->payment_stage;
        } elseif (!$transaction->payment_stage) {
            // Default stage jika belum ada: sesuaikan dengan grup
            $group = PaymentGroup::find($paymentGroupId);
            $updateData['payment_stage'] = ($group && $group->transactions()->where('status', 'approved')->count() > 0) ? 'proses' : 'uang_muka';
        }

        $transaction->update($updateData);

        // Sinkronkan status Payment Group setelah transaksi disetujui
        if ($paymentGroupId) {
            PaymentGroup::find($paymentGroupId)?->syncStatus();
        }

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
            'reason'         => $request->reason,
            'created_at'     => now(),
        ]);

        // Transaksi ditolak → Payment Group TIDAK diperbarui (sesuai spec)
        // Status Payment Group hanya dihitung dari transaksi approved

        return back()->with('success', 'Transaksi ditolak dan dicatat');
    }

    // ---------------------------------------------------------------
    // AJAX — Cek apakah ada Payment Group aktif untuk proyek+kategori+tipe
    // Endpoint: GET /transactions/check-payment-group
    // ---------------------------------------------------------------
    public function checkPaymentGroup(Request $request)
    {
        $request->validate([
            'project_id'  => 'required|integer|exists:projects,id',
            'category_id' => 'required|integer|exists:categories,id',
            'type'        => 'nullable|in:pemasukan,pengeluaran',
        ]);

        $type = $request->get('type', 'pengeluaran');

        $group = PaymentGroup::where('project_id', $request->project_id)
            ->where('category_id', $request->category_id)
            ->where('type', $type)
            ->with(['project:id,project_name', 'category:id,category_name'])
            ->orderByDesc('id')
            ->first();

        if (!$group) {
            return response()->json([
                'has_group'          => false,
                'has_active_group'   => false,
                'is_completed'       => false,
                'needs_confirmation' => false,
            ]);
        }

        $isCompleted = $group->payment_status === 'selesai';

        return response()->json([
            'has_group'          => true,
            'has_active_group'   => !$isCompleted,
            'is_completed'       => $isCompleted,
            'needs_confirmation' => $isCompleted,
            'payment_status'     => $group->payment_status,
            'group' => [
                'id'             => $group->id,
                'type'           => $group->type,
                'label'          => $group->label,
                'payment_status' => $group->payment_status,
                'total_amount'   => $group->total_approved,
                'project_name'   => $group->project->project_name ?? '-',
                'category_name'  => $group->category->category_name ?? '-',
            ],
        ]);
    }

    // ---------------------------------------------------------------
    // PRIVATE — Deteksi / buat Payment Group yang sesuai
    // ---------------------------------------------------------------
    private function resolvePaymentGroup(
        int $projectId,
        int $categoryId,
        string $type,
        ?string $action,
        ?string $label
    ): int {
        $existing = PaymentGroup::where('project_id', $projectId)
            ->where('category_id', $categoryId)
            ->where('type', $type)
            ->orderByDesc('id')
            ->first();

        // Belum ada Payment Group sama sekali → buat baru otomatis
        if (!$existing) {
            return PaymentGroup::create([
                'project_id'     => $projectId,
                'category_id'    => $categoryId,
                'type'           => $type,
                'payment_status' => 'uang_muka',
            ])->id;
        }

        // Status belum Selesai → gabung ke kelompok aktif
        if ($existing->payment_status !== 'selesai') {
            return $existing->id;
        }

        // Status Selesai → jika admin memilih 'lanjutkan', gunakan group lama
        if ($action === 'lanjutkan') {
            return $existing->id;
        }

        // Status Selesai → jika admin memilih 'baru', buat group baru dengan label
        if ($action === 'baru') {
            return PaymentGroup::create([
                'project_id'     => $projectId,
                'category_id'    => $categoryId,
                'type'           => $type,
                'payment_status' => 'uang_muka',
                'label'          => $label,
            ])->id;
        }

        return $existing->id;
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
