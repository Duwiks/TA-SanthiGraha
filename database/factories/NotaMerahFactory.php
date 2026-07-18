<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Project;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\NotaMerah>
 */
class NotaMerahFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'                 => User::factory()->pegawai(),
            'project_id'              => Project::factory(),
            'category_id'             => Category::factory(),
            'description'             => fake()->sentence(),
            'amount'                  => fake()->numberBetween(50000, 10000000),
            'nota_date'               => now()->subDays(1)->format('Y-m-d'),
            'bank_tujuan'             => fake()->randomElement(['BRI', 'BNI', 'BCA', 'Mandiri']),
            'no_rekening'             => fake()->numerify('##########'),
            'nama_pemilik_rekening'   => fake()->name(),
            'nota_photo'              => 'nota-merah/dummy.jpg',
            'realisasi_photo'         => null,
            'realisasi_date'          => null,
            'transfer_proof'          => null,
            'status'                  => 'menunggu_persetujuan',
            'rejection_reason'        => null,
            'approved_by'             => null,
            'confirmed_at'            => null,
        ];
    }

    /**
     * State: menunggu persetujuan admin.
     */
    public function menungguPersetujuan(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'menunggu_persetujuan',
        ]);
    }

    /**
     * State: ditolak admin.
     */
    public function ditolak(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'           => 'ditolak',
            'rejection_reason' => 'Data tidak lengkap.',
            'approved_by'      => User::factory()->admin(),
        ]);
    }

    /**
     * State: menunggu konfirmasi pegawai (transfer sudah dilakukan admin).
     */
    public function menungguKonfirmasi(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'         => 'menunggu_konfirmasi',
            'transfer_proof' => 'nota-merah/transfer/dummy.jpg',
            'approved_by'    => User::factory()->admin(),
        ]);
    }

    /**
     * State: menunggu verifikasi admin (pegawai sudah upload realisasi).
     */
    public function menungguVerifikasi(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'          => 'menunggu_verifikasi',
            'transfer_proof'  => 'nota-merah/transfer/dummy.jpg',
            'realisasi_photo' => 'nota-merah/realisasi/dummy.jpg',
            'realisasi_date'  => now()->subDays(1)->format('Y-m-d'),
            'approved_by'     => User::factory()->admin(),
        ]);
    }

    /**
     * State: selesai (sudah dikonfirmasi admin).
     */
    public function selesai(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'          => 'selesai',
            'transfer_proof'  => 'nota-merah/transfer/dummy.jpg',
            'realisasi_photo' => 'nota-merah/realisasi/dummy.jpg',
            'realisasi_date'  => now()->subDays(1)->format('Y-m-d'),
            'approved_by'     => User::factory()->admin(),
            'confirmed_at'    => now(),
        ]);
    }
}
