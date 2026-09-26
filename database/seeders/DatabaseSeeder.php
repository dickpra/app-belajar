<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Buat atau Update Akun Admin
        User::updateOrCreate(
            ['email' => 'admin@admin.com'], // Cari berdasarkan email ini
            [
                'name' => 'Administrator',
                'password' => Hash::make('admin123'),
                'role' => 'admin'
            ]
        );

        // 2. Buat atau Update Akun Teacher (Guru)
        User::updateOrCreate(
            ['email' => 'guru@admin.com'], // Cari berdasarkan email ini
            [
                'name' => 'Teacher', 
                'password' => Hash::make('guru'), 
                'role' => 'teacher'
            ]
        );
    }
}