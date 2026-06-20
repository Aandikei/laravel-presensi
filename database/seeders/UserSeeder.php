<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@app.com'],
            ['name' => 'Super Admin', 'password' => Hash::make('password'), 'email_verified_at' => now()]
        );
        $superAdmin->assignRole('super_admin');
    }
}
