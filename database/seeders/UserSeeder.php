<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin User
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@hq.app',
            'role' => 'admin',
            'position' => 'System Administrator',
            'id_number' => 'HQI-001',
            'password' => Hash::make('password'),
        ]);

        // Standard User
        User::create([
            'name' => 'Regular User',
            'email' => 'user@hq.app',
            'role' => 'user',
            'position' => 'Staff Member',
            'id_number' => 'HQI-002',
            'password' => Hash::make('password'),
        ]);
    }
}
