<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\User::create([
            'name' => 'CV Santhi Graha',
            'username' => 'santhigraha',
            'password' => bcrypt('santhigraha2026'),
            'role' => 'admin',
            'phone' => '080000000000',
        ]);
    }
}
