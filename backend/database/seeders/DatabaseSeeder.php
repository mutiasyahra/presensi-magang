<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // User Admin
        \App\Models\User::create([
            'name' => 'Super Admin',
            'email' => 'admin@bps.go.id',
            'password' => bcrypt('password123'),
            'role' => 'admin'
        ]);

        // User Mahasiswa Biasa
        \App\Models\User::create([
            'name' => 'Student Demo',
            'email' => 'student@university.edu',
            'password' => bcrypt('password123'),
            'role' => 'user'
        ]);
    }
}
