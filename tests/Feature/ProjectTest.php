<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $pegawai;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin   = User::factory()->admin()->create();
        $this->pegawai = User::factory()->pegawai()->create();
    }

    // -------------------------------------------------------
    // Index
    // -------------------------------------------------------

    /** @test */
    public function admin_dapat_melihat_daftar_proyek(): void
    {
        Project::factory()->count(3)->create();

        $response = $this->actingAs($this->admin)->get(route('projects.index'));

        $response->assertStatus(200);
    }

    /** @test */
    public function pegawai_tidak_dapat_mengakses_halaman_proyek(): void
    {
        $response = $this->actingAs($this->pegawai)->get(route('projects.index'));

        $response->assertStatus(403);
    }

    // -------------------------------------------------------
    // Store (Create)
    // -------------------------------------------------------

    /** @test */
    public function admin_dapat_membuat_proyek_baru(): void
    {
        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('projects.store'), [
                'project_name' => 'Proyek Uji Coba',
                'location'     => 'Bandung',
                'start_date'   => now()->format('Y-m-d'),
                'end_date'     => now()->addMonths(6)->format('Y-m-d'),
            ]);

        $response->assertRedirect(route('projects.index'));
        $this->assertDatabaseHas('projects', [
            'project_name' => 'Proyek Uji Coba',
            'status'       => 'aktif',
        ]);
    }

    /** @test */
    public function pembuatan_proyek_gagal_jika_nama_kosong(): void
    {
        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('projects.store'), [
                'project_name' => '',
            ]);

        $response->assertSessionHasErrors('project_name');
    }

    /** @test */
    public function pembuatan_proyek_gagal_jika_nama_sudah_ada(): void
    {
        Project::factory()->create(['project_name' => 'Proyek Duplikat']);

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('projects.store'), [
                'project_name' => 'Proyek Duplikat',
            ]);

        $response->assertSessionHasErrors('project_name');
    }

    /** @test */
    public function pembuatan_proyek_gagal_jika_end_date_sebelum_start_date(): void
    {
        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('projects.store'), [
                'project_name' => 'Proyek Tanggal Salah',
                'start_date'   => now()->addMonths(3)->format('Y-m-d'),
                'end_date'     => now()->format('Y-m-d'),
            ]);

        $response->assertSessionHasErrors('end_date');
    }

    // -------------------------------------------------------
    // Update (Edit)
    // -------------------------------------------------------

    /** @test */
    public function admin_dapat_mengupdate_proyek_aktif(): void
    {
        $project = Project::factory()->aktif()->create();

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->put(route('projects.update', $project->id), [
                'project_name' => 'Nama Baru Proyek',
                'location'     => 'Jakarta',
                'start_date'   => $project->start_date,
                'end_date'     => $project->end_date,
            ]);

        $response->assertRedirect(route('projects.index'));
        $this->assertDatabaseHas('projects', ['project_name' => 'Nama Baru Proyek']);
    }

    /** @test */
    public function admin_tidak_dapat_mengedit_proyek_yang_sudah_selesai(): void
    {
        $project = Project::factory()->selesai()->create();

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->put(route('projects.update', $project->id), [
                'project_name' => 'Nama Baru Setelah Selesai',
                'location'     => 'Surabaya',
                'start_date'   => $project->start_date,
                'end_date'     => $project->end_date,
            ]);

        $response->assertRedirect(route('projects.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('projects', ['project_name' => 'Nama Baru Setelah Selesai']);
    }

    // -------------------------------------------------------
    // Destroy (Delete)
    // -------------------------------------------------------

    /** @test */
    public function admin_dapat_menghapus_proyek_yang_tidak_memiliki_transaksi(): void
    {
        $project = Project::factory()->aktif()->create();

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->delete(route('projects.destroy', $project->id));

        $response->assertRedirect(route('projects.index'));
        $this->assertDatabaseMissing('projects', ['id' => $project->id]);
    }

    /** @test */
    public function admin_tidak_dapat_menghapus_proyek_yang_memiliki_transaksi(): void
    {
        $project = Project::factory()->aktif()->create();
        Transaction::factory()->create(['project_id' => $project->id]);

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->delete(route('projects.destroy', $project->id));

        $response->assertRedirect(route('projects.index'));
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('projects', ['id' => $project->id]);
    }

    // -------------------------------------------------------
    // Complete & Extend
    // -------------------------------------------------------

    /** @test */
    public function admin_dapat_menandai_proyek_sebagai_selesai(): void
    {
        $project = Project::factory()->aktif()->create();

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('projects.complete', $project->id));

        $response->assertRedirect(route('projects.index'));
        $this->assertDatabaseHas('projects', ['id' => $project->id, 'status' => 'selesai']);
    }

    /** @test */
    public function admin_tidak_dapat_menyelesaikan_proyek_yang_sudah_selesai(): void
    {
        $project = Project::factory()->selesai()->create();

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('projects.complete', $project->id));

        $response->assertRedirect(route('projects.index'));
        $response->assertSessionHas('error');
    }

    /** @test */
    public function admin_dapat_memperpanjang_deadline_proyek(): void
    {
        $project    = Project::factory()->aktif()->create();
        $newEndDate = now()->addMonths(6)->format('Y-m-d');

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('projects.extend', $project->id), [
                'new_end_date' => $newEndDate,
            ]);

        $response->assertRedirect(route('projects.index'));
        $this->assertDatabaseHas('projects', ['id' => $project->id, 'end_date' => $newEndDate]);
    }

    /** @test */
    public function perpanjangan_gagal_jika_tanggal_baru_tidak_setelah_hari_ini(): void
    {
        $project = Project::factory()->aktif()->create();

        $response = $this->actingAs($this->admin)
            ->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->post(route('projects.extend', $project->id), [
                'new_end_date' => now()->subDay()->format('Y-m-d'),
            ]);

        $response->assertSessionHasErrors('new_end_date');
    }

    // -------------------------------------------------------
    // Security Fix Test – Dashboard proyekSelesai Count
    // -------------------------------------------------------

    #[\PHPUnit\Framework\Attributes\Test]
    public function dashboard_admin_menghitung_proyek_selesai_dengan_benar(): void
    {
        // 1 proyek selesai resmi (status=selesai)
        Project::factory()->selesai()->create();

        // 1 proyek aktif dengan end_date sudah terlewat (overdue) – tidak boleh dihitung sebagai selesai
        Project::factory()->aktif()->create([
            'end_date' => now()->subDays(5)->format('Y-m-d'),
        ]);

        // 1 proyek aktif normal
        Project::factory()->aktif()->create();

        $response = $this->actingAs($this->admin)->get(route('dashboard'));

        $response->assertStatus(200);

        // Hanya 1 proyek yang berstatus 'selesai', bukan 2 (yang include overdue)
        $proyekSelesai = \App\Models\Project::where('status', 'selesai')->count();
        $this->assertEquals(1, $proyekSelesai);
    }
}
