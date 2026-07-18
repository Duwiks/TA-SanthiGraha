<?php

namespace Tests\Unit;

use App\Models\NotaMerah;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotaMerahModelTest extends TestCase
{
    use RefreshDatabase;

    // -------------------------------------------------------
    // Accessor: status_label
    // -------------------------------------------------------

    /** @test */
    public function status_label_menunggu_persetujuan(): void
    {
        $nota = NotaMerah::factory()->menungguPersetujuan()->create();

        $this->assertEquals('Menunggu Persetujuan', $nota->status_label);
    }

    /** @test */
    public function status_label_ditolak(): void
    {
        $nota = NotaMerah::factory()->ditolak()->create();

        $this->assertEquals('Ditolak', $nota->status_label);
    }

    /** @test */
    public function status_label_menunggu_konfirmasi_shows_menunggu_realisasi(): void
    {
        $nota = NotaMerah::factory()->menungguKonfirmasi()->create();

        $this->assertEquals('Menunggu Realisasi', $nota->status_label);
    }

    /** @test */
    public function status_label_menunggu_verifikasi(): void
    {
        $nota = NotaMerah::factory()->menungguVerifikasi()->create();

        $this->assertEquals('Menunggu Verifikasi', $nota->status_label);
    }

    /** @test */
    public function status_label_selesai(): void
    {
        $nota = NotaMerah::factory()->selesai()->create();

        $this->assertEquals('Selesai', $nota->status_label);
    }

    // -------------------------------------------------------
    // Accessor: status_color
    // -------------------------------------------------------

    /** @test */
    public function status_color_menunggu_persetujuan_is_amber(): void
    {
        $nota = NotaMerah::factory()->menungguPersetujuan()->create();

        $this->assertEquals('bg-amber-100 text-amber-700', $nota->status_color);
    }

    /** @test */
    public function status_color_ditolak_is_red(): void
    {
        $nota = NotaMerah::factory()->ditolak()->create();

        $this->assertEquals('bg-red-100 text-red-700', $nota->status_color);
    }

    /** @test */
    public function status_color_menunggu_konfirmasi_is_blue(): void
    {
        $nota = NotaMerah::factory()->menungguKonfirmasi()->create();

        $this->assertEquals('bg-blue-100 text-blue-700', $nota->status_color);
    }

    /** @test */
    public function status_color_menunggu_verifikasi_is_purple(): void
    {
        $nota = NotaMerah::factory()->menungguVerifikasi()->create();

        $this->assertEquals('bg-purple-100 text-purple-700', $nota->status_color);
    }

    /** @test */
    public function status_color_selesai_is_emerald(): void
    {
        $nota = NotaMerah::factory()->selesai()->create();

        $this->assertEquals('bg-emerald-100 text-emerald-700', $nota->status_color);
    }
}
