<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\Project;
use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id'          => User::factory(),
            'project_id'       => Project::factory(),
            'category_id'      => Category::factory(),
            'transaction_date' => fake()->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'type'             => fake()->randomElement(['pemasukan', 'pengeluaran']),
            'description'      => fake()->sentence(),
            'amount'           => fake()->numberBetween(10000, 5000000),
            'payment_method'   => fake()->randomElement(['Transfer Bank', 'Tunai', 'BRI', 'BNI']),
            'receipt_photo'    => null,
            'status'           => 'pending',
            'approved_by'      => null,
            'nota_merah_id'    => null,
        ];
    }

    /**
     * State untuk transaksi yang sudah disetujui.
     */
    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'      => 'approved',
            'approved_by' => User::factory()->admin(),
        ]);
    }

    /**
     * State untuk transaksi yang ditolak.
     */
    public function rejected(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'rejected',
        ]);
    }

    /**
     * State untuk transaksi pemasukan.
     */
    public function pemasukan(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'pemasukan',
        ]);
    }

    /**
     * State untuk transaksi pengeluaran.
     */
    public function pengeluaran(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'pengeluaran',
        ]);
    }
}
