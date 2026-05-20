<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cache
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Buat permission
        $permissions = [
            'manage-users',
            'manage-roles',
            'manage-permissions',
            'view-dashboard',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Buat role & assign permission
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $admin      = Role::firstOrCreate(['name' => 'admin']);
        $user       = Role::firstOrCreate(['name' => 'user']);

        // Super admin dapat semua permission
        $superAdmin->givePermissionTo(Permission::all());

        // Admin dapat sebagian
        $admin->givePermissionTo(['manage-users', 'view-dashboard']);

        // User dapat minimal
        $user->givePermissionTo(['view-dashboard']);
    }
}
