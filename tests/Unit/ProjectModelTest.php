<?php

namespace Tests\Unit;

use App\Models\Project;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectModelTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------
    // Accessor: is_finished
    // -------------------------------------------------------

    /** @test */
    public function project_with_status_selesai_is_finished(): void
    {
        $project = Project::factory()->selesai()->create();

        $this->assertTrue($project->is_finished);
    }

    /** @test */
    public function project_with_status_aktif_is_not_finished(): void
    {
        $project = Project::factory()->aktif()->create();

        $this->assertFalse($project->is_finished);
    }

    // -------------------------------------------------------
    // Accessor: is_overdue
    // -------------------------------------------------------

    /** @test */
    public function active_project_with_past_end_date_is_overdue(): void
    {
        $project = Project::factory()->overdue()->create();

        $this->assertTrue($project->is_overdue);
    }

    /** @test */
    public function active_project_with_future_end_date_is_not_overdue(): void
    {
        $project = Project::factory()->aktif()->create(); // end_date +3 bulan

        $this->assertFalse($project->is_overdue);
    }

    /** @test */
    public function finished_project_is_not_overdue_even_if_end_date_passed(): void
    {
        $project = Project::factory()->selesai()->create([
            'end_date' => now()->subDays(5)->format('Y-m-d'),
        ]);

        $this->assertFalse($project->is_overdue);
    }

    /** @test */
    public function active_project_with_null_end_date_is_not_overdue(): void
    {
        $project = Project::factory()->aktif()->create([
            'end_date' => null,
        ]);

        $this->assertFalse($project->is_overdue);
    }

    // -------------------------------------------------------
    // Scope: scopeActive
    // -------------------------------------------------------

    /** @test */
    public function scope_active_returns_only_active_projects_with_future_end_date(): void
    {
        Project::factory()->aktif()->count(3)->create();
        Project::factory()->selesai()->count(2)->create();
        Project::factory()->overdue()->count(1)->create();

        $activeCount = Project::active()->count();

        // 3 aktif (belum lewat) + 1 overdue (aktif tapi end_date terlewat, tidak masuk scope)
        // scopeActive hanya ambil aktif DAN (end_date null ATAU end_date >= hari ini)
        $this->assertEquals(3, $activeCount);
    }
}
