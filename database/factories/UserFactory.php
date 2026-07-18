<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name'     => fake()->name(),
            'username' => fake()->unique()->userName(),
            'password' => 'password123',
            'role'     => 'pegawai',
            'phone'    => fake()->phoneNumber(),
        ];
    }

    /**
     * State untuk user dengan role admin.
     */
    public function admin(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'admin',
        ]);
    }

    /**
     * State untuk user dengan role pegawai.
     */
    public function pegawai(): static
    {
        return $this->state(fn (array $attributes) => [
            'role' => 'pegawai',
        ]);
    }
}
