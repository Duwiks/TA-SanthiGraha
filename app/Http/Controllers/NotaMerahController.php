<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NotaMerah;
use App\Models\Transaction;
use App\Models\Category;
use App\Models\Project;
use Illuminate\Support\Facades\Storage;

class NotaMerahController extends Controller
{
    // ---------------------------------------------------------------
    // INDEX – Daftar Nota Merah
    // Pegawai: hanya milik sendiri
    // Admin  : semua nota merah + filter status
    // ---------------------------------------------------------------
    public function index(Request $request)
    {
        $query = NotaMerah::with([
            'user:id,name',
            'project:id,project_name',
            'category:id,category_name',
            'approver:id,name',
        ]);

        if (auth()->user()->role === 'pegawai') {
            $query->where('user_id', auth()->id());
        }

        // Filter status (admin & pegawai)
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('description', 'like', "%{$search}%")
                    ->orWhereHas('project', fn($qp) => $qp->where('project_name', 'like', "%{$search}%"))
                    ->orWhereHas('category', fn($qc) => $qc->where('category_name', 'like', "%{$search}%"));
            });
        }

        $notaMerahs = $query->orderBy('created_at', 'desc')->orderBy('id', 'desc')->paginate(10)->withQueryString();

        if (auth()->user()->role === 'admin') {
            // Hitung antrean yang butuh aksi admin
            $countMenungguPersetujuan = NotaMerah::where('status', 'menunggu_persetujuan')->count();
            $countMenungguKonfirmasi  = NotaMerah::whereIn('status', ['menunggu_verifikasi'])->count();
            return view('admin.nota-merah', compact('notaMerahs', 'countMenungguPersetujuan', 'countMenungguKonfirmasi'));
        }

        return view('nota-merah.index', compact('notaMerahs'));
    }

    // ---------------------------------------------------------------
    // CREATE – Form Pengajuan Nota Merah (Pegawai)
    // ---------------------------------------------------------------
    public function create()
    {
        if (auth()->user()->role !== 'pegawai') {
            abort(403);
        }

        $categories = Category::all();
        $projects = Project::active()->get();

        return view('nota-merah.create', compact('categories', 'projects'));
    }

    // ---------------------------------------------------------------
    // STORE – Simpan Pengajuan Nota Merah
    // ---------------------------------------------------------------
    public function store(Request $request)
    {
        if (auth()->user()->role !== 'pegawai') {
            abort(403);
        }

        $request->validate([
            'project_id'             => 'required|exists:projects,id',
            'category_id'            => 'required|exists:categories,id',
            'description'            => 'nullable|string',
            'amount'                 => 'required|numeric|gt:0',
            'bank_tujuan'            => 'required|string|max:100',
            'no_rekening'            => 'required|regex:/^[0-9]+$/|max:50',
            'nama_pemilik_rekening'  => 'required|regex:/^[a-zA-Z\s]+$/|max:150',
            'nota_photo'             => 'required|file|mimes:jpeg,png,jpg,pdf|max:20480',
        ], [
            'project_id.required'            => 'Proyek wajib dipilih.',
            'project_id.exists'              => 'Proyek tidak valid.',
            'category_id.required'           => 'Kategori wajib dipilih.',
            'category_id.exists'             => 'Kategori tidak valid.',
            'amount.required'                => 'Nominal wajib diisi.',
            'amount.numeric'                 => 'Nominal harus berupa angka.',
            'amount.gt'                      => 'Nominal harus lebih besar dari Rp 0.',
            'bank_tujuan.required'           => 'Bank tujuan wajib diisi.',
            'no_rekening.required'           => 'No. rekening wajib diisi.',
            'no_rekening.regex'              => 'No. rekening hanya boleh berisi angka.',
            'nama_pemilik_rekening.required' => 'Nama pemilik rekening wajib diisi.',
            'nama_pemilik_rekening.regex'    => 'Nama pemilik rekening hanya boleh berisi huruf dan spasi.',
            'nota_photo.required'            => 'Foto nota merah wajib dilampirkan.',
            'nota_photo.file'                => 'File tidak valid.',
            'nota_photo.mimes'               => 'Foto harus berupa JPG, PNG, atau PDF.',
            'nota_photo.max'                 => 'Ukuran file maksimal 20 MB.',
        ]);

        $notaPath = $this->handleUpload($request->file('nota_photo'), 'nota-merah');

        NotaMerah::create([
            'user_id'                => auth()->id(),
            'project_id'             => $request->project_id,
            'category_id'            => $request->category_id,
            'description'            => $request->description,
            'amount'                 => $request->amount,
            'bank_tujuan'            => $request->bank_tujuan,
            'no_rekening'            => $request->no_rekening,
            'nama_pemilik_rekening'  => $request->nama_pemilik_rekening,
            'nota_photo'             => $notaPath,
            'status'                 => 'menunggu_persetujuan',
        ]);

        return redirect()->route('nota-merah.index')
            ->with('success', 'Pengajuan nota merah berhasil dikirim! Menunggu persetujuan Admin.');
    }

    // ---------------------------------------------------------------
    // SHOW – Detail Nota Merah
    // ---------------------------------------------------------------
    public function show($id)
    {
        $nota = NotaMerah::with([
            'user',
            'project',
            'category',
            'approver',
            'transaction',
        ])->findOrFail($id);

        // Pegawai hanya boleh lihat milik sendiri
        if (auth()->user()->role === 'pegawai' && $nota->user_id !== auth()->id()) {
            abort(403);
        }

        return view('nota-merah.show', compact('nota'));
    }

    // ---------------------------------------------------------------
    // APPROVE FORM – Admin melihat form upload bukti transfer
    // ---------------------------------------------------------------
    public function approveForm($id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $nota = NotaMerah::with(['user', 'project', 'category'])->findOrFail($id);

        if ($nota->status !== 'menunggu_persetujuan') {
            return redirect()->route('nota-merah.show', $id)
                ->with('error', 'Nota merah ini tidak dalam status menunggu persetujuan.');
        }

        return view('nota-merah.approve-transfer', compact('nota'));
    }

    // ---------------------------------------------------------------
    // STORE APPROVE – Admin upload bukti transfer → menunggu_konfirmasi
    // ---------------------------------------------------------------
    public function storeApprove(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $nota = NotaMerah::findOrFail($id);

        if ($nota->status !== 'menunggu_persetujuan') {
            return redirect()->route('nota-merah.show', $id)
                ->with('error', 'Nota merah ini tidak dalam status menunggu persetujuan.');
        }

        $request->validate([
            'transfer_proof' => 'required|file|mimes:jpeg,png,jpg,pdf|max:5120',
        ], [
            'transfer_proof.required' => 'Bukti transfer wajib dilampirkan.',
            'transfer_proof.mimes'    => 'File harus berupa JPG, PNG, atau PDF.',
            'transfer_proof.max'      => 'Ukuran file maksimal 5 MB.',
        ]);

        $transferPath = $this->handleUpload($request->file('transfer_proof'), 'nota-merah/transfer');

        $nota->update([
            'transfer_proof' => $transferPath,
            'status'         => 'menunggu_konfirmasi',
            'approved_by'    => auth()->id(),
        ]);

        return redirect()->route('nota-merah.show', $id)
            ->with('success', 'Nota merah disetujui dan bukti transfer berhasil diupload! Pegawai dapat melakukan pembelian dan mengupload bukti realisasi.');
    }

    // ---------------------------------------------------------------
    // REJECT – Admin Menolak Nota Merah
    // ---------------------------------------------------------------
    public function reject(Request $request, $id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $nota = NotaMerah::findOrFail($id);

        if ($nota->status !== 'menunggu_persetujuan') {
            return back()->with('error', 'Nota merah ini tidak dalam status menunggu persetujuan.');
        }

        $request->validate([
            'reason' => 'required|string|max:500',
        ], [
            'reason.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $nota->update([
            'status'           => 'ditolak',
            'rejection_reason' => $request->reason,
            'approved_by'      => auth()->id(),
        ]);

        return back()->with('success', 'Nota merah ditolak dan pegawai sudah diberitahu.');
    }

    // ---------------------------------------------------------------
    // UPLOAD REALISASI FORM – Pegawai upload bukti realisasi
    // ---------------------------------------------------------------
    public function realisasiForm($id)
    {
        if (auth()->user()->role !== 'pegawai') {
            abort(403);
        }

        $nota = NotaMerah::with(['project', 'category'])->findOrFail($id);

        if ($nota->user_id !== auth()->id()) {
            abort(403);
        }

        if ($nota->status !== 'menunggu_konfirmasi') {
            return redirect()->route('nota-merah.show', $id)
                ->with('error', 'Nota merah ini belum dapat diupload bukti realisasinya saat ini.');
        }

        return view('nota-merah.upload-realisasi', compact('nota'));
    }

    // ---------------------------------------------------------------
    // STORE REALISASI – Simpan bukti realisasi → menunggu verifikasi admin
    // ---------------------------------------------------------------
    public function storeRealisasi(Request $request, $id)
    {
        if (auth()->user()->role !== 'pegawai') {
            abort(403);
        }

        $nota = NotaMerah::findOrFail($id);

        if ($nota->user_id !== auth()->id()) {
            abort(403);
        }

        if ($nota->status !== 'menunggu_konfirmasi') {
            return redirect()->route('nota-merah.show', $id)
                ->with('error', 'Nota merah ini tidak dapat diupload bukti realisasinya saat ini.');
        }

        $request->validate([
            'realisasi_photo' => 'required|file|mimes:jpeg,png,jpg,pdf|max:20480',
            'realisasi_date'  => 'required|date',
        ], [
            'realisasi_photo.required' => 'Bukti realisasi (foto struk / kwitansi) wajib dilampirkan.',
            'realisasi_photo.mimes'    => 'Format file harus berupa jpeg, png, jpg, atau pdf.',
            'realisasi_photo.max'      => 'Ukuran file terlalu besar (maksimal 20MB).',
            'realisasi_date.required'  => 'Tanggal realisasi belanja wajib diisi.',
        ]);

        $realisasiPath = $this->handleUpload($request->file('realisasi_photo'), 'nota-merah/realisasi');

        // Simpan foto realisasi, status → menunggu_verifikasi (admin perlu verifikasi dulu)
        $nota->update([
            'realisasi_photo' => $realisasiPath,
            'realisasi_date'  => $request->realisasi_date,
            'status'          => 'menunggu_verifikasi',
        ]);

        return redirect()->route('nota-merah.index')
            ->with('success', 'Bukti realisasi berhasil diupload! Menunggu verifikasi Admin sebelum dicatat di kas.');
    }

    // ---------------------------------------------------------------
    // CONFIRM – Admin Verifikasi Realisasi → Auto-create Transaction + Selesai
    // ---------------------------------------------------------------
    public function confirm($id)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $nota = NotaMerah::findOrFail($id);

        if ($nota->status !== 'menunggu_verifikasi') {
            return back()->with('error', 'Nota merah ini tidak dalam status menunggu verifikasi realisasi.');
        }

        // Buat transaksi resmi di tabel transactions
        Transaction::create([
            'user_id'          => $nota->user_id,
            'project_id'       => $nota->project_id,
            'category_id'      => $nota->category_id,
            'transaction_date' => $nota->realisasi_date,
            'type'             => 'pengeluaran',
            'description'      => $nota->description ? '[Nota Merah] ' . $nota->description : '[Nota Merah #' . $nota->id . ']',
            'amount'           => $nota->amount,
            'payment_method'   => $nota->bank_tujuan ?? 'Transfer',
            'receipt_photo'    => $nota->realisasi_photo,
            'status'           => 'approved',
            'approved_by'      => auth()->id(),
            'nota_merah_id'    => $nota->id,
        ]);

        // Update nota merah → selesai
        $nota->update([
            'status'       => 'selesai',
            'confirmed_at' => now(),
            'approved_by'  => auth()->id(),
        ]);

        return back()->with('success', 'Realisasi dikonfirmasi! Transaksi telah tercatat resmi di buku kas.');
    }

    // ---------------------------------------------------------------
    // EDIT – Form Edit Nota Merah yang Ditolak (Pegawai)
    // ---------------------------------------------------------------
    public function edit($id)
    {
        if (auth()->user()->role !== 'pegawai') {
            abort(403);
        }

        $nota = NotaMerah::with(['project', 'category'])->findOrFail($id);

        if ($nota->user_id !== auth()->id()) {
            abort(403);
        }

        if ($nota->status !== 'ditolak') {
            return redirect()->route('nota-merah.show', $id)
                ->with('error', 'Hanya nota merah yang ditolak yang dapat diedit ulang.');
        }

        $categories = Category::all();
        $projects = Project::active()->get();

        return view('nota-merah.edit', compact('nota', 'categories', 'projects'));
    }

    // ---------------------------------------------------------------
    // UPDATE – Simpan Edit Nota Merah yang Ditolak → Kembali menunggu_persetujuan
    // ---------------------------------------------------------------
    public function update(Request $request, $id)
    {
        if (auth()->user()->role !== 'pegawai') {
            abort(403);
        }

        $nota = NotaMerah::findOrFail($id);

        if ($nota->user_id !== auth()->id()) {
            abort(403);
        }

        if ($nota->status !== 'ditolak') {
            return redirect()->route('nota-merah.show', $id)
                ->with('error', 'Hanya nota merah yang ditolak yang dapat diedit ulang.');
        }

        $request->validate([
            'project_id'             => 'required|exists:projects,id',
            'category_id'            => 'required|exists:categories,id',
            'description'            => 'nullable|string',
            'amount'                 => 'required|numeric|min:0',
            'bank_tujuan'            => 'required|string|max:100',
            'no_rekening'            => 'required|regex:/^[0-9]+$/|max:50',
            'nama_pemilik_rekening'  => 'required|regex:/^[a-zA-Z\s]+$/|max:150',
            'nota_photo'             => 'nullable|file|mimes:jpeg,png,jpg,pdf|max:20480',
        ], [
            'project_id.required'            => 'Proyek wajib dipilih.',
            'category_id.required'           => 'Kategori wajib dipilih.',
            'amount.required'                => 'Nominal wajib diisi.',
            'bank_tujuan.required'           => 'Bank tujuan wajib diisi.',
            'no_rekening.required'           => 'No. rekening wajib diisi.',
            'no_rekening.regex'              => 'No. rekening hanya boleh berisi angka.',
            'nama_pemilik_rekening.required' => 'Nama pemilik rekening wajib diisi.',
            'nama_pemilik_rekening.regex'    => 'Nama pemilik rekening hanya boleh berisi huruf dan spasi.',
            'nota_photo.mimes'               => 'Format file harus berupa jpeg, png, jpg, atau pdf.',
            'nota_photo.max'                 => 'Ukuran file terlalu besar (maksimal 20MB).',
        ]);

        $data = [
            'project_id'             => $request->project_id,
            'category_id'            => $request->category_id,
            'description'            => $request->description,
            'amount'                 => $request->amount,
            'bank_tujuan'            => $request->bank_tujuan,
            'no_rekening'            => $request->no_rekening,
            'nama_pemilik_rekening'  => $request->nama_pemilik_rekening,
            'status'                 => 'menunggu_persetujuan',
            'rejection_reason'       => null,
            'approved_by'            => null,
        ];

        if ($request->hasFile('nota_photo')) {
            // Hapus file lama
            if ($nota->nota_photo && \Illuminate\Support\Facades\Storage::disk('public')->exists($nota->nota_photo)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($nota->nota_photo);
            }
            $data['nota_photo'] = $this->handleUpload($request->file('nota_photo'), 'nota-merah');
        }

        $nota->update($data);

        return redirect()->route('nota-merah.index')
            ->with('success', 'Pengajuan nota merah berhasil diperbarui dan dikirim ulang untuk persetujuan Admin.');
    }

    // ---------------------------------------------------------------
    // DESTROY – Hapus Nota Merah (hanya status menunggu/ditolak oleh pegawai)
    // ---------------------------------------------------------------
    public function destroy($id)
    {
        $nota = NotaMerah::findOrFail($id);

        // Pegawai hanya bisa hapus milik sendiri yang belum diproses
        if (auth()->user()->role === 'pegawai') {
            if ($nota->user_id !== auth()->id()) {
                abort(403);
            }
            if (!in_array($nota->status, ['menunggu_persetujuan', 'ditolak'])) {
                return back()->with('error', 'Nota merah yang sudah disetujui tidak dapat dihapus.');
            }
        }

        // Hapus file terlampir
        foreach (['nota_photo', 'realisasi_photo', 'transfer_proof'] as $field) {
            if ($nota->$field && Storage::disk('public')->exists($nota->$field)) {
                Storage::disk('public')->delete($nota->$field);
            }
        }

        $nota->delete();

        return redirect()->route('nota-merah.index')->with('success', 'Nota merah berhasil dihapus.');
    }

    // ---------------------------------------------------------------
    // HELPER – Upload file dengan server-side compression
    // ---------------------------------------------------------------
    private function handleUpload($file, string $folder): string
    {
        if (
            in_array(strtolower($file->extension()), ['jpg', 'jpeg', 'png'])
            && $file->getSize() > 1024 * 1024
        ) {
            $sourceImage = strtolower($file->extension()) === 'png'
                ? @imagecreatefrompng($file->getRealPath())
                : @imagecreatefromjpeg($file->getRealPath());

            if ($sourceImage !== false) {
                $dir = storage_path("app/public/{$folder}");
                if (!file_exists($dir)) {
                    mkdir($dir, 0755, true);
                }
                $path = $dir . '/' . uniqid() . '_compressed.jpg';
                imagejpeg($sourceImage, $path, 60);
                imagedestroy($sourceImage);
                return "{$folder}/" . basename($path);
            }
        }

        return $file->store($folder, 'public');
    }
}
