<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@app.com'],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make('password'),
            ]
        );
        $superAdmin->assignRole('super_admin');

        // Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@app.com'],
            [
                'name'     => 'Admin',
                'password' => Hash::make('password'),
            ]
        );
        $admin->assignRole('admin');

        // User biasa
        $user = User::firstOrCreate(
            ['email' => 'user@app.com'],
            [
                'name'     => 'User',
                'password' => Hash::make('password'),
            ]
        );
        $user->assignRole('user');
    }
}
