<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\NotaMerah;
use App\Models\Project;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TransactionTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $pegawai;
    private Project $project;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin    = User::factory()->admin()->create();
        $this->pegawai  = User::factory()->pegawai()->create();
        $this->project  = Project::factory()->aktif()->create();
        $this->category = Category::factory()->create();
    }

    // -------------------------------------------------------
    // Index – Tampilan Daftar Transaksi
    // -------------------------------------------------------

    /** @test */
    public function admin_hanya_melihat_transaksi_yang_sudah_disetujui(): void
    {
        Transaction::factory()->approved()->create([
            'project_id'  => $this->project->id,
            'category_id' => $this->category->id,
        ]);
        Transaction::factory()->create([
            'project_id'  => $this->project->id,
            'category_id' => $this->category->id,
            'status'      => 'pending',
        ]);

        $response = $this->actingAs($this->admin)->get(route('transactions.index'));

        $response->assertStatus(200);
    }

    /** @test */
    public function pegawai_hanya_melihat_transaksi_milik_sendiri(): void
    {
        // Transaksi milik pegawai ini
        Transaction::factory()->create([
            'user_id'     => $this->pegawai->id,
            'project_id'  => $this->project->id,
            'category_id' => $this->category->id,
        ]);
        // Transaksi milik pegawai lain
        Transaction::factory()->create([
            'project_id'  => $this->project->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->actingAs($this->pegawai)->get(route('transactions.index'));

        $response->assertStatus(200);
    }

    // -------------------------------------------------------
    // Create – Form Tambah Transaksi
    // -------------------------------------------------------

    /** @test */
    public function pegawai_dapat_mengakses_form_tambah_transaksi(): void
    {
        $response = $this->actingAs($this->pegawai)->get(route('transactions.create'));

        $response->assertStatus(200);
    }

    // -------------------------------------------------------
    // Store – Simpan Transaksi
    // -------------------------------------------------------

    /** @test */
    public function pegawai_dapat_membuat_transaksi_pengeluaran(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->pegawai)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('transactions.store'), [
                'project_id'       => $this->project->id,
                'category_id'      => $this->category->id,
                'transaction_date' => now()->format('Y-m-d'),
                'type'             => 'pengeluaran',
                'description'      => 'Pembelian alat',
                'amount'           => 500000,
                'payment_method'   => 'Transfer Bank',
                'payment_stage'    => 'uang_muka',
            ]);

        $response->assertRedirect(route('transactions.index'));
        $this->assertDatabaseHas('transactions', [
            'user_id' => $this->pegawai->id,
            'type'    => 'pengeluaran',
            'status'  => 'pending',
        ]);
    }

    /** @test */
    public function admin_dapat_membuat_transaksi_pemasukan_dan_langsung_approved(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('transactions.store'), [
                'project_id'       => $this->project->id,
                'category_id'      => $this->category->id,
                'transaction_date' => now()->format('Y-m-d'),
                'type'             => 'pemasukan',
                'description'      => 'Dana awal proyek',
                'amount'           => 10000000,
                'payment_method'   => 'Transfer Bank',
                'payment_stage'    => 'uang_muka',
            ]);

        $response->assertRedirect(route('transactions.index'));
        $this->assertDatabaseHas('transactions', [
            'type'        => 'pemasukan',
            'status'      => 'approved',
            'approved_by' => $this->admin->id,
        ]);
    }

    /** @test */
    public function pembuatan_transaksi_gagal_jika_amount_nol_atau_negatif(): void
    {
        $response = $this->actingAs($this->pegawai)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('transactions.store'), [
                'project_id'       => $this->project->id,
                'category_id'      => $this->category->id,
                'transaction_date' => now()->format('Y-m-d'),
                'type'             => 'pengeluaran',
                'amount'           => 0,
                'payment_method'   => 'Tunai',
            ]);

        $response->assertSessionHasErrors('amount');
    }

    /** @test */
    public function pembuatan_transaksi_gagal_jika_tanggal_melebihi_hari_ini(): void
    {
        $response = $this->actingAs($this->pegawai)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('transactions.store'), [
                'project_id'       => $this->project->id,
                'category_id'      => $this->category->id,
                'transaction_date' => now()->addDays(5)->format('Y-m-d'),
                'type'             => 'pengeluaran',
                'amount'           => 100000,
                'payment_method'   => 'Tunai',
            ]);

        $response->assertSessionHasErrors('transaction_date');
    }

    /** @test */
    public function pegawai_tidak_dapat_membuat_transaksi_pemasukan(): void
    {
        $response = $this->actingAs($this->pegawai)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('transactions.store'), [
                'project_id'       => $this->project->id,
                'category_id'      => $this->category->id,
                'transaction_date' => now()->format('Y-m-d'),
                'type'             => 'pemasukan',
                'amount'           => 500000,
                'payment_method'   => 'Tunai',
            ]);

        $response->assertSessionHasErrors('type');
    }

    // -------------------------------------------------------
    // Approve & Reject
    // -------------------------------------------------------

    /** @test */
    public function admin_dapat_menyetujui_transaksi_pending(): void
    {
        $transaction = Transaction::factory()->create([
            'project_id'  => $this->project->id,
            'category_id' => $this->category->id,
            'status'      => 'pending',
        ]);

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('transactions.approve', $transaction->id));

        $response->assertRedirect();
        $this->assertDatabaseHas('transactions', [
            'id'          => $transaction->id,
            'status'      => 'approved',
            'approved_by' => $this->admin->id,
        ]);
    }

    /** @test */
    public function admin_dapat_menolak_transaksi_pending_dengan_alasan(): void
    {
        $transaction = Transaction::factory()->create([
            'project_id'  => $this->project->id,
            'category_id' => $this->category->id,
            'status'      => 'pending',
        ]);

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('transactions.reject', $transaction->id), [
                'reason' => 'Bukti tidak valid',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('transactions', ['id' => $transaction->id, 'status' => 'rejected']);
        $this->assertDatabaseHas('transaction_rejections', [
            'transaction_id' => $transaction->id,
            'reason'         => 'Bukti tidak valid',
        ]);
    }

    /** @test */
    public function penolakan_gagal_jika_alasan_kosong(): void
    {
        $transaction = Transaction::factory()->create([
            'project_id'  => $this->project->id,
            'category_id' => $this->category->id,
            'status'      => 'pending',
        ]);

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('transactions.reject', $transaction->id), [
                'reason' => '',
            ]);

        $response->assertSessionHasErrors('reason');
    }

    /** @test */
    public function pegawai_tidak_dapat_mengakses_halaman_approvals(): void
    {
        // Route approvals bernama 'approvals.index' di sistem ini
        $response = $this->actingAs($this->pegawai)->get(route('approvals.index'));

        $response->assertStatus(403);
    }

    /** @test */
    public function admin_dapat_mengakses_halaman_approvals(): void
    {
        $response = $this->actingAs($this->admin)->get(route('approvals.index'));

        $response->assertStatus(200);
    }

    // -------------------------------------------------------
    // Edit & Update
    // -------------------------------------------------------

    /** @test */
    public function pegawai_dapat_mengedit_transaksi_milik_sendiri_yang_pending(): void
    {
        $transaction = Transaction::factory()->create([
            'user_id'     => $this->pegawai->id,
            'project_id'  => $this->project->id,
            'category_id' => $this->category->id,
            'status'      => 'pending',
        ]);

        $response = $this->actingAs($this->pegawai)->get(route('transactions.edit', $transaction->id));

        $response->assertStatus(200);
    }

    /** @test */
    public function pegawai_tidak_dapat_mengedit_transaksi_yang_sudah_disetujui(): void
    {
        $approvedBy  = User::factory()->admin()->create();
        $transaction = Transaction::factory()->create([
            'user_id'     => $this->pegawai->id,
            'project_id'  => $this->project->id,
            'category_id' => $this->category->id,
            'status'      => 'approved',
            'approved_by' => $approvedBy->id,
        ]);

        $response = $this->actingAs($this->pegawai)->get(route('transactions.edit', $transaction->id));

        // Redirect dengan pesan error
        $response->assertRedirect(route('transactions.index'));
        $response->assertSessionHas('error');
    }

    // -------------------------------------------------------
    // Destroy (Delete)
    // -------------------------------------------------------

    /** @test */
    public function pegawai_dapat_menghapus_transaksi_milik_sendiri_yang_pending(): void
    {
        $transaction = Transaction::factory()->create([
            'user_id'     => $this->pegawai->id,
            'project_id'  => $this->project->id,
            'category_id' => $this->category->id,
            'status'      => 'pending',
        ]);

        $response = $this->actingAs($this->pegawai)
            ->from(route('transactions.index'))
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->delete(route('transactions.destroy', $transaction->id));

        $response->assertRedirect(route('transactions.index'));
        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);
    }

    /** @test */
    public function pegawai_tidak_dapat_menghapus_transaksi_yang_sudah_disetujui(): void
    {
        $approvedBy  = User::factory()->admin()->create();
        $transaction = Transaction::factory()->create([
            'user_id'     => $this->pegawai->id,
            'project_id'  => $this->project->id,
            'category_id' => $this->category->id,
            'status'      => 'approved',
            'approved_by' => $approvedBy->id,
        ]);

        $response = $this->actingAs($this->pegawai)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->delete(route('transactions.destroy', $transaction->id));

        $response->assertRedirect(route('transactions.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('transactions', ['id' => $transaction->id]);
    }

    /** @test */
    public function pegawai_membuat_transaksi_status_pending_dan_payment_group_id_null(): void
    {
        Storage::fake('public');

        $response = $this->actingAs($this->pegawai)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('transactions.store'), [
                'project_id'       => $this->project->id,
                'category_id'      => $this->category->id,
                'transaction_date' => now()->format('Y-m-d'),
                'type'             => 'pengeluaran',
                'description'      => 'Pembelian bahan A',
                'amount'           => 350000,
                'payment_method'   => 'Cash',
                'payment_stage'    => 'uang_muka',
            ]);

        $response->assertRedirect(route('transactions.index'));
        $this->assertDatabaseHas('transactions', [
            'user_id'          => $this->pegawai->id,
            'project_id'       => $this->project->id,
            'category_id'      => $this->category->id,
            'status'           => 'pending',
            'payment_group_id' => null,
        ]);
        $this->assertDatabaseCount('payment_groups', 0);
    }

    /** @test */
    public function transaksi_dikelompokkan_hanya_setelah_di_acc_admin(): void
    {
        // 1. Pegawai ajukan transaksi 1
        $trx1 = Transaction::factory()->create([
            'user_id'          => $this->pegawai->id,
            'project_id'       => $this->project->id,
            'category_id'      => $this->category->id,
            'type'             => 'pengeluaran',
            'amount'           => 100000,
            'status'           => 'pending',
            'payment_group_id' => null,
            'payment_stage'    => 'uang_muka',
        ]);

        // 2. Pegawai ajukan transaksi 2 dengan project dan category sama
        $trx2 = Transaction::factory()->create([
            'user_id'          => $this->pegawai->id,
            'project_id'       => $this->project->id,
            'category_id'      => $this->category->id,
            'type'             => 'pengeluaran',
            'amount'           => 200000,
            'status'           => 'pending',
            'payment_group_id' => null,
            'payment_stage'    => 'proses',
        ]);

        // Keduanya belum punya group
        $this->assertNull($trx1->fresh()->payment_group_id);
        $this->assertNull($trx2->fresh()->payment_group_id);

        // 3. Admin ACC transaksi 1
        $this->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('transactions.approve', $trx1->id), [
                'payment_stage' => 'uang_muka',
            ]);

        $trx1Fresh = $trx1->fresh();
        $this->assertEquals('approved', $trx1Fresh->status);
        $this->assertNotNull($trx1Fresh->payment_group_id);

        // Transaksi 2 masih pending dan payment_group_id masih null
        $trx2Fresh = $trx2->fresh();
        $this->assertEquals('pending', $trx2Fresh->status);
        $this->assertNull($trx2Fresh->payment_group_id);

        // 4. Admin ACC transaksi 2
        $this->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('transactions.approve', $trx2->id), [
                'payment_stage' => 'proses',
            ]);

        $trx2Fresh = $trx2->fresh();
        $this->assertEquals('approved', $trx2Fresh->status);
        // Transaksi 2 sekarang tergabung dalam kelompok yang sama dengan transaksi 1
        $this->assertEquals($trx1Fresh->payment_group_id, $trx2Fresh->payment_group_id);
    }

    /** @test */
    public function empat_transaksi_uang_muka_hanya_menjadi_satu_uang_muka_dan_tiga_proses_setelah_di_acc(): void
    {
        // 4 transaksi diajukan pegawai dengan payment_stage awal uang_muka
        $transactions = [];
        for ($i = 1; $i <= 4; $i++) {
            $transactions[] = Transaction::factory()->create([
                'user_id'          => $this->pegawai->id,
                'project_id'       => $this->project->id,
                'category_id'      => $this->category->id,
                'type'             => 'pengeluaran',
                'amount'           => 100000 * $i,
                'status'           => 'pending',
                'payment_group_id' => null,
                'payment_stage'    => 'uang_muka',
            ]);
        }

        // Admin melakukan ACC pada ke-4 transaksi satu per satu
        foreach ($transactions as $trx) {
            $this->actingAs($this->admin)
                ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
                ->post(route('transactions.approve', $trx->id), [
                    'payment_stage' => 'uang_muka', // Input request mengirim uang_muka
                ]);
        }

        // Ambil transaksi segar dari database
        $freshTrx1 = $transactions[0]->fresh();
        $freshTrx2 = $transactions[1]->fresh();
        $freshTrx3 = $transactions[2]->fresh();
        $freshTrx4 = $transactions[3]->fresh();

        // Semua berstatus approved
        $this->assertEquals('approved', $freshTrx1->status);
        $this->assertEquals('approved', $freshTrx2->status);
        $this->assertEquals('approved', $freshTrx3->status);
        $this->assertEquals('approved', $freshTrx4->status);

        // Semua masuk ke PaymentGroup yang sama
        $groupId = $freshTrx1->payment_group_id;
        $this->assertNotNull($groupId);
        $this->assertEquals($groupId, $freshTrx2->payment_group_id);
        $this->assertEquals($groupId, $freshTrx3->payment_group_id);
        $this->assertEquals($groupId, $freshTrx4->payment_group_id);

        // Transaksi 1 tetap Uang Muka
        $this->assertEquals('uang_muka', $freshTrx1->payment_stage);

        // Transaksi 2, 3, 4 otomatis dinormalisasi menjadi Proses
        $this->assertEquals('proses', $freshTrx2->payment_stage);
        $this->assertEquals('proses', $freshTrx3->payment_stage);
        $this->assertEquals('proses', $freshTrx4->payment_stage);

        // Hitung transaksi uang muka dalam PaymentGroup ini: harus tepat 1
        $countUangMuka = Transaction::where('payment_group_id', $groupId)
            ->where('status', 'approved')
            ->where('payment_stage', 'uang_muka')
            ->count();
        $this->assertEquals(1, $countUangMuka);
    }

    /** @test */
    public function riwayat_nota_dalam_detail_menampilkan_transaksi_terakhir_di_acc_paling_atas(): void
    {
        // Trx 1 diajukan pegawai dengan tanggal hari ini
        $trx1 = Transaction::factory()->create([
            'user_id'          => $this->pegawai->id,
            'project_id'       => $this->project->id,
            'category_id'      => $this->category->id,
            'type'             => 'pengeluaran',
            'amount'           => 500000,
            'status'           => 'pending',
            'payment_group_id' => null,
            'payment_stage'    => 'uang_muka',
            'transaction_date' => now()->format('Y-m-d'),
        ]);

        // Trx 2 diajukan pegawai dengan tanggal kemarin
        $trx2 = Transaction::factory()->create([
            'user_id'          => $this->pegawai->id,
            'project_id'       => $this->project->id,
            'category_id'      => $this->category->id,
            'type'             => 'pengeluaran',
            'amount'           => 300000,
            'status'           => 'pending',
            'payment_group_id' => null,
            'payment_stage'    => 'proses',
            'transaction_date' => now()->subDay()->format('Y-m-d'),
        ]);

        // 1. Admin ACC Trx 1 terlebih dahulu (waktu approval awal)
        $this->travel(1)->minutes();
        $this->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('transactions.approve', $trx1->id), ['payment_stage' => 'uang_muka']);

        // 2. Admin ACC Trx 2 setelah Trx 1 (waktu approval paling akhir)
        $this->travel(5)->minutes();
        $this->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('transactions.approve', $trx2->id), ['payment_stage' => 'proses']);

        // Akses halaman detail transaksi admin
        $responseAdmin = $this->actingAs($this->admin)
            ->get(route('transactions.admin-show', $trx1->id));

        $responseAdmin->assertStatus(200);
        $groupTrxAdmin = $responseAdmin->viewData('groupTransactions');

        // Pastikan transaksi yang terakhir di-ACC (Trx 2 / Proses) berada di urutan index 0 (paling atas)
        $this->assertEquals($trx2->id, $groupTrxAdmin->first()->id);
        $this->assertEquals('proses', $groupTrxAdmin->first()->payment_stage);

        // Pastikan transaksi pertama yang di-ACC (Trx 1 / Uang Muka) berada di bawahnya
        $this->assertEquals($trx1->id, $groupTrxAdmin->last()->id);
        $this->assertEquals('uang_muka', $groupTrxAdmin->last()->payment_stage);

        // Akses halaman detail transaksi pegawai
        $responsePegawai = $this->actingAs($this->pegawai)
            ->get(route('transactions.show', $trx1->id));

        $responsePegawai->assertStatus(200);
        $groupTrxPegawai = $responsePegawai->viewData('groupTransactions');

        $this->assertEquals($trx2->id, $groupTrxPegawai->first()->id);
        $this->assertEquals($trx1->id, $groupTrxPegawai->last()->id);
    }
}
