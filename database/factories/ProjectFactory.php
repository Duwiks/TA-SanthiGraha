<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-1 year', 'now');
        $end   = fake()->dateTimeBetween($start, '+1 year');

        return [
            'project_name' => fake()->unique()->words(3, true),
            'location'     => fake()->city(),
            'start_date'   => $start->format('Y-m-d'),
            'end_date'     => $end->format('Y-m-d'),
            'status'       => 'aktif',
        ];
    }

    /**
     * State untuk proyek yang sudah selesai.
     */
    public function selesai(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'selesai',
        ]);
    }

    /**
     * State untuk proyek yang masih aktif.
     */
    public function aktif(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'   => 'aktif',
            'end_date' => now()->addMonths(3)->format('Y-m-d'),
        ]);
    }

    /**
     * State untuk proyek yang sudah jatuh tempo (overdue).
     */
    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'   => 'aktif',
            'end_date' => now()->subDays(10)->format('Y-m-d'),
        ]);
    }
}
