<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        User::create([
            'name' => 'CV Santhi Graha',
            'username' => 'santhigraha',
            'password' => Hash::make('santhigraha2026'),
            'role' => 'admin',
            'phone' => '080000000000',
        ]);
    }
}