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

class NotaMerahTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $pegawai;
    private Project $project;
    private Category $category;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->admin    = User::factory()->admin()->create();
        $this->pegawai  = User::factory()->pegawai()->create();
        $this->project  = Project::factory()->aktif()->create();
        $this->category = Category::factory()->create();
    }

    // -------------------------------------------------------
    // Index
    // -------------------------------------------------------

    /** @test */
    public function pegawai_dapat_melihat_daftar_nota_merah_milik_sendiri(): void
    {
        NotaMerah::factory()->create([
            'user_id'     => $this->pegawai->id,
            'project_id'  => $this->project->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->actingAs($this->pegawai)->get(route('nota-merah.index'));

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_dapat_melihat_semua_nota_merah(): void
    {
        NotaMerah::factory()->count(3)->create([
            'project_id'  => $this->project->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('nota-merah.index'));

        $response->assertStatus(200);
    }

    // -------------------------------------------------------
    // Create Form
    // -------------------------------------------------------

    /** @test */
    public function pegawai_dapat_mengakses_form_buat_nota_merah(): void
    {
        $response = $this->actingAs($this->pegawai)->get(route('nota-merah.create'));

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_tidak_dapat_mengakses_form_buat_nota_merah(): void
    {
        $response = $this->actingAs($this->admin)->get(route('nota-merah.create'));

        $response->assertStatus(403);
    }

    // -------------------------------------------------------
    // Store – Pegawai Mengajukan Nota Merah
    // -------------------------------------------------------

    /** @test */
    public function pegawai_dapat_mengajukan_nota_merah(): void
    {
        $fotoNota = UploadedFile::fake()->image('nota.jpg');

        $response = $this->actingAs($this->pegawai)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('nota-merah.store'), [
                'project_id'             => $this->project->id,
                'category_id'            => $this->category->id,
                'description'            => 'Pembelian material proyek',
                'amount'                 => 750000,
                'nota_date'              => now()->subDay()->format('Y-m-d'),
                'bank_tujuan'            => 'BRI',
                'no_rekening'            => '1234567890',
                'nama_pemilik_rekening'  => 'Budi Santoso',
                'nota_photo'             => $fotoNota,
            ]);

        $response->assertRedirect(route('nota-merah.index'));
        $this->assertDatabaseHas('nota_merah', [
            'user_id' => $this->pegawai->id,
            'status'  => 'menunggu_persetujuan',
        ]);
    }

    /** @test */
    public function pengajuan_nota_merah_gagal_jika_nota_photo_tidak_ada(): void
    {
        $response = $this->actingAs($this->pegawai)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('nota-merah.store'), [
                'project_id'             => $this->project->id,
                'category_id'            => $this->category->id,
                'amount'                 => 750000,
                'nota_date'              => now()->subDay()->format('Y-m-d'),
                'bank_tujuan'            => 'BRI',
                'no_rekening'            => '1234567890',
                'nama_pemilik_rekening'  => 'Budi Santoso',
            ]);

        $response->assertSessionHasErrors('nota_photo');
    }

    /** @test */
    public function pengajuan_nota_merah_gagal_jika_no_rekening_mengandung_huruf(): void
    {
        $fotoNota = UploadedFile::fake()->image('nota.jpg');

        $response = $this->actingAs($this->pegawai)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('nota-merah.store'), [
                'project_id'             => $this->project->id,
                'category_id'            => $this->category->id,
                'amount'                 => 750000,
                'nota_date'              => now()->subDay()->format('Y-m-d'),
                'bank_tujuan'            => 'BRI',
                'no_rekening'            => 'abc123def',
                'nama_pemilik_rekening'  => 'Budi Santoso',
                'nota_photo'             => $fotoNota,
            ]);

        $response->assertSessionHasErrors('no_rekening');
    }

    // -------------------------------------------------------
    // Approve Form – Admin Menyetujui
    // (Route: GET  nota-merah/{id}/approve  → nota-merah.approve.form)
    // (Route: POST nota-merah/{id}/approve  → nota-merah.approve.store)
    // -------------------------------------------------------

    /** @test */
    public function admin_dapat_mengakses_form_persetujuan_nota_merah(): void
    {
        $nota = NotaMerah::factory()->menungguPersetujuan()->create([
            'project_id'  => $this->project->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->actingAs($this->admin)->get(route('nota-merah.approve.form', $nota->id));

        $response->assertStatus(200);
    }

    /** @test */
    public function admin_dapat_menyetujui_nota_merah_dengan_upload_bukti_transfer(): void
    {
        $nota         = NotaMerah::factory()->menungguPersetujuan()->create([
            'user_id'     => $this->pegawai->id,
            'project_id'  => $this->project->id,
            'category_id' => $this->category->id,
        ]);
        $buktTransfer = UploadedFile::fake()->image('transfer.jpg');

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('nota-merah.approve.store', $nota->id), [
                'transfer_proof' => $buktTransfer,
            ]);

        $response->assertRedirect(route('nota-merah.show', $nota->id));
        $this->assertDatabaseHas('nota_merah', [
            'id'          => $nota->id,
            'status'      => 'menunggu_konfirmasi',
            'approved_by' => $this->admin->id,
        ]);
    }

    // -------------------------------------------------------
    // Reject – Admin Menolak
    // (Route: POST nota-merah/{id}/reject  → nota-merah.reject)
    // -------------------------------------------------------

    /** @test */
    public function admin_dapat_menolak_nota_merah_yang_menunggu_persetujuan(): void
    {
        $nota = NotaMerah::factory()->menungguPersetujuan()->create([
            'user_id'     => $this->pegawai->id,
            'project_id'  => $this->project->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('nota-merah.reject', $nota->id), [
                'reason' => 'Data rekening tidak sesuai',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('nota_merah', [
            'id'               => $nota->id,
            'status'           => 'ditolak',
            'rejection_reason' => 'Data rekening tidak sesuai',
        ]);
    }

    /** @test */
    public function penolakan_nota_merah_gagal_jika_alasan_kosong(): void
    {
        $nota = NotaMerah::factory()->menungguPersetujuan()->create([
            'project_id'  => $this->project->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('nota-merah.reject', $nota->id), [
                'reason' => '',
            ]);

        $response->assertSessionHasErrors('reason');
    }

    // -------------------------------------------------------
    // Realisasi – Pegawai Upload Bukti Pembelian
    // (Route: GET  nota-merah/{id}/realisasi  → nota-merah.realisasi.form)
    // (Route: POST nota-merah/{id}/realisasi  → nota-merah.realisasi.store)
    // -------------------------------------------------------

    /** @test */
    public function pegawai_dapat_mengakses_form_upload_realisasi(): void
    {
        $nota = NotaMerah::factory()->menungguKonfirmasi()->create([
            'user_id'     => $this->pegawai->id,
            'project_id'  => $this->project->id,
            'category_id' => $this->category->id,
            'approved_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->pegawai)->get(route('nota-merah.realisasi.form', $nota->id));

        $response->assertStatus(200);
    }

    /** @test */
    public function pegawai_dapat_mengupload_bukti_realisasi(): void
    {
        $nota          = NotaMerah::factory()->menungguKonfirmasi()->create([
            'user_id'     => $this->pegawai->id,
            'project_id'  => $this->project->id,
            'category_id' => $this->category->id,
            'approved_by' => $this->admin->id,
        ]);
        $buktiRealisasi = UploadedFile::fake()->image('realisasi.jpg');

        $response = $this->actingAs($this->pegawai)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('nota-merah.realisasi.store', $nota->id), [
                'realisasi_photo' => $buktiRealisasi,
            ]);

        $response->assertRedirect(route('nota-merah.index'));
        $this->assertDatabaseHas('nota_merah', [
            'id'     => $nota->id,
            'status' => 'menunggu_verifikasi',
        ]);
    }

    // -------------------------------------------------------
    // Verifikasi Realisasi – Admin
    // (Route: POST nota-merah/{id}/reject-realisasi  → nota-merah.reject-realisasi)
    // (Route: POST nota-merah/{id}/confirm           → nota-merah.confirm)
    // -------------------------------------------------------

    /** @test */
    public function admin_dapat_menolak_bukti_realisasi(): void
    {
        $nota = NotaMerah::factory()->menungguVerifikasi()->create([
            'user_id'     => $this->pegawai->id,
            'project_id'  => $this->project->id,
            'category_id' => $this->category->id,
            'approved_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('nota-merah.reject-realisasi', $nota->id), [
                'reason' => 'Foto tidak jelas',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('nota_merah', [
            'id'               => $nota->id,
            'status'           => 'menunggu_konfirmasi',
            'rejection_reason' => 'Foto tidak jelas',
        ]);
    }

    /** @test */
    public function admin_dapat_mengkonfirmasi_realisasi_dan_membuat_transaksi_otomatis(): void
    {
        $nota = NotaMerah::factory()->menungguVerifikasi()->create([
            'user_id'     => $this->pegawai->id,
            'project_id'  => $this->project->id,
            'category_id' => $this->category->id,
            'approved_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('nota-merah.confirm', $nota->id));

        $response->assertRedirect();
        // Nota merah harus menjadi selesai
        $this->assertDatabaseHas('nota_merah', [
            'id'     => $nota->id,
            'status' => 'selesai',
        ]);
        // Transaksi harus otomatis dibuat
        $this->assertDatabaseHas('transactions', [
            'nota_merah_id' => $nota->id,
            'status'        => 'approved',
            'type'          => 'pengeluaran',
        ]);
    }

    // -------------------------------------------------------
    // Edit – Pegawai Edit Nota yang Ditolak
    // -------------------------------------------------------

    /** @test */
    public function pegawai_dapat_mengedit_nota_merah_yang_ditolak(): void
    {
        $nota = NotaMerah::factory()->ditolak()->create([
            'user_id'     => $this->pegawai->id,
            'project_id'  => $this->project->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->actingAs($this->pegawai)->get(route('nota-merah.edit', $nota->id));

        $response->assertStatus(200);
    }

    /** @test */
    public function pegawai_tidak_dapat_mengedit_nota_merah_yang_sedang_diproses(): void
    {
        $nota = NotaMerah::factory()->menungguKonfirmasi()->create([
            'user_id'     => $this->pegawai->id,
            'project_id'  => $this->project->id,
            'category_id' => $this->category->id,
            'approved_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->pegawai)->get(route('nota-merah.edit', $nota->id));

        $response->assertRedirect(route('nota-merah.show', $nota->id));
        $response->assertSessionHas('error');
    }

    // -------------------------------------------------------
    // Destroy – Pegawai Hapus Nota Merah
    // -------------------------------------------------------

    /** @test */
    public function pegawai_dapat_menghapus_nota_merah_yang_masih_menunggu(): void
    {
        $nota = NotaMerah::factory()->menungguPersetujuan()->create([
            'user_id'     => $this->pegawai->id,
            'project_id'  => $this->project->id,
            'category_id' => $this->category->id,
        ]);

        $response = $this->actingAs($this->pegawai)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->delete(route('nota-merah.destroy', $nota->id));

        $response->assertRedirect(route('nota-merah.index'));
        $this->assertDatabaseMissing('nota_merah', ['id' => $nota->id]);
    }

    /** @test */
    public function pegawai_tidak_dapat_menghapus_nota_merah_yang_sudah_disetujui(): void
    {
        $nota = NotaMerah::factory()->menungguKonfirmasi()->create([
            'user_id'     => $this->pegawai->id,
            'project_id'  => $this->project->id,
            'category_id' => $this->category->id,
            'approved_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->pegawai)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->delete(route('nota-merah.destroy', $nota->id));

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('nota_merah', ['id' => $nota->id]);
    }

    // -------------------------------------------------------
    // Security Fixes Tests – Verifikasi Perbaikan Keamanan
    // -------------------------------------------------------

    #[\PHPUnit\Framework\Attributes\Test]
    public function admin_tidak_dapat_menghapus_nota_merah_yang_sudah_selesai(): void
    {
        // Buat nota merah berstatus selesai
        $nota = NotaMerah::factory()->selesai()->create([
            'user_id'     => $this->pegawai->id,
            'project_id'  => $this->project->id,
            'category_id' => $this->category->id,
            'approved_by' => $this->admin->id,
        ]);

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->delete(route('nota-merah.destroy', $nota->id));

        // Harus ditolak dengan pesan error – nota merah selesai tidak boleh dihapus
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('nota_merah', ['id' => $nota->id]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function menghapus_transaksi_dari_nota_merah_mengembalikan_status_nota_ke_menunggu_verifikasi(): void
    {
        // Buat nota merah selesai dengan transaksi terkait
        $nota = NotaMerah::factory()->selesai()->create([
            'user_id'     => $this->pegawai->id,
            'project_id'  => $this->project->id,
            'category_id' => $this->category->id,
            'approved_by' => $this->admin->id,
        ]);

        // Buat transaksi terkait nota merah
        $transaction = Transaction::factory()->approved()->create([
            'user_id'       => $this->pegawai->id,
            'project_id'    => $this->project->id,
            'category_id'   => $this->category->id,
            'nota_merah_id' => $nota->id,
            'approved_by'   => $this->admin->id,
        ]);

        // Admin menghapus transaksi (aksi yang valid)
        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->delete(route('transactions.destroy', $transaction->id));

        $response->assertRedirect(route('transactions.index'));

        // Transaksi harus terhapus
        $this->assertDatabaseMissing('transactions', ['id' => $transaction->id]);

        // Nota merah harus kembali ke status menunggu_verifikasi (bukan tetap selesai)
        $this->assertDatabaseHas('nota_merah', [
            'id'           => $nota->id,
            'status'       => 'menunggu_verifikasi',
            'confirmed_at' => null,
        ]);
    }
}
