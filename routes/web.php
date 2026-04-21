<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\PegawaiController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\RekapController;

Route::get('/', function () {
    return redirect('/dashboard');
});

// Auth Routes (Public)
Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);

Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);

// Protected Core Routes
Route::middleware(['auth'])->group(function () {
    // Dashboard Logic
    Route::get(
        '/dashboard',
        function () {
            if (auth()->user()->role === 'admin') {
                $totalPegawai = \App\Models\User::where('role', 'pegawai')->count();
                $proyekAktif = \App\Models\Project::count();
                $menungguApproval = \App\Models\Transaction::where('status', 'pending')->count();
                $totalTransaksi = \App\Models\Transaction::count();

                // Chart Logic (Pemasukan vs Pengeluaran per Bulan)
                $projectId = request('project_id');
                $year = date('Y');

                $chartQuery = \App\Models\Transaction::where('status', 'approved')
                    ->whereYear('transaction_date', $year);

                if ($projectId) {
                    $chartQuery->where('project_id', $projectId);
                }

                $chartTransactions = $chartQuery->get(['amount', 'type', 'transaction_date']);

                $months = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agt', 'Sep', 'Okt', 'Nov', 'Des'];
                $pemasukanData = array_fill(0, 12, 0);
                $pengeluaranData = array_fill(0, 12, 0);

                foreach ($chartTransactions as $trx) {
                    $monthIndex = (int)date('n', strtotime($trx->transaction_date)) - 1;
                    if ($trx->type == 'pemasukan') {
                        $pemasukanData[$monthIndex] += $trx->amount;
                    } elseif ($trx->type == 'pengeluaran') {
                        $pengeluaranData[$monthIndex] += $trx->amount;
                    }
                }

                $projects = \App\Models\Project::orderBy('project_name')->get();

                return view('admin.dashboard', compact(
                    'totalPegawai', 'proyekAktif', 'menungguApproval', 'totalTransaksi',
                    'months', 'pemasukanData', 'pengeluaranData', 'projects', 'projectId', 'year'
                ));
            } else {
                $transaksiDiajukan = \App\Models\Transaction::where('user_id', auth()->id())->count();
                $menungguProses = \App\Models\Transaction::where('user_id', auth()->id())->where('status', 'pending')->count();
                $transaksiDitolak = \App\Models\Transaction::where('user_id', auth()->id())->where('status', 'rejected')->count();
                return view('pegawai.dashboard', compact('transaksiDiajukan', 'menungguProses', 'transaksiDitolak'));
            }
        }
    )->name('dashboard');

    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Categories Web CRUD Endpoints
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::get('/categories/create', [CategoryController::class, 'create'])->name('categories.create');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::get('/categories/{id}/edit', [CategoryController::class, 'edit'])->name('categories.edit');
    Route::put('/categories/{id}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // Projects Web CRUD Endpoints
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::get('/projects/create', [ProjectController::class, 'create'])->name('projects.create');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{id}/edit', [ProjectController::class, 'edit'])->name('projects.edit');
    Route::put('/projects/{id}', [ProjectController::class, 'update'])->name('projects.update');
    Route::delete('/projects/{id}', [ProjectController::class, 'destroy'])->name('projects.destroy');

    // Dedicated Transaction Web Endpoints
    Route::get('/transactions', [TransactionController::class, 'index'])->name('transactions.index');
    Route::get('/approvals', [TransactionController::class, 'approvals'])->name('approvals.index');
    Route::get('/transactions/create', [TransactionController::class, 'create'])->name('transactions.create');
    Route::post('/transactions', [TransactionController::class, 'store'])->name('transactions.store');
    Route::get('/transactions/{id}/edit', [TransactionController::class, 'edit'])->name('transactions.edit');
    Route::put('/transactions/{id}', [TransactionController::class, 'update'])->name('transactions.update');
    Route::delete('/transactions/{id}', [TransactionController::class, 'destroy'])->name('transactions.destroy');

    // Approval / Rejection Logic
    Route::post('/transactions/{id}/approve', [TransactionController::class, 'approve'])->name('transactions.approve');
    Route::post('/transactions/{id}/reject', [TransactionController::class, 'reject'])->name('transactions.reject');

    // Rekap & Laporan Route
    Route::get('/rekap', [RekapController::class, 'index'])->name('rekap.index');
    Route::get('/rekap/export', [RekapController::class, 'export'])->name('rekap.export');

    // Data Akun Pegawai
    Route::get('/pegawai', [PegawaiController::class, 'index'])->name('pegawai.index');
    Route::put('/pegawai/{id}/reset-password', [PegawaiController::class, 'resetPassword'])->name('pegawai.resetPassword');

    // Pengaturan Akun
    Route::get('/account', [AccountController::class, 'index'])->name('account.index');
    Route::put('/account/password', [AccountController::class, 'updatePassword'])->name('account.password');
});
