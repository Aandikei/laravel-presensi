<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Permissions per fitur
        $permissions = [
            // Instansi
            'manage-instansi',

            // User & Role
            'manage-users',
            'manage-roles',
            'manage-permissions',

            // Master Data
            'manage-tahun-ajaran',
            'manage-guru',
            'manage-siswa',
            'manage-kelas',
            'manage-mapel',
            'manage-jadwal',
            'manage-kurikulum',
            'manage-registrasi',

            // View only (untuk kepala sekolah & wakil)
            'view-guru',
            'view-siswa',
            'view-kelas',

            // Absensi
            'input-absensi',
            'edit-absensi',
            'lock-absensi',
            'view-absensi',

            // Poin
            'manage-master-poin',
            'manage-poin-siswa',
            'view-poin',

            // Laporan
            'view-laporan',
            'export-laporan',

            // Settings
            'manage-settings',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Super Admin → semua permission
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->givePermissionTo(Permission::all());

        // Admin Sekolah
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo([
            'manage-tahun-ajaran',
            'manage-guru',
            'manage-siswa',
            'manage-kelas',
            'manage-mapel',
            'manage-jadwal',
            'manage-kurikulum',
            'manage-registrasi',
            'lock-absensi',
            'view-absensi',
            'manage-master-poin',
            'manage-poin-siswa',
            'view-poin',
            'view-laporan',
            'export-laporan',
            'manage-settings',
        ]);

        // Guru
        $guru = Role::firstOrCreate(['name' => 'guru']);
        $guru->givePermissionTo([
            'input-absensi',
            'edit-absensi',
            'view-absensi',
            'view-laporan',
            'export-laporan',
        ]);

        // Wali Kelas (extends guru)
        $waliKelas = Role::firstOrCreate(['name' => 'wali_kelas']);
        $waliKelas->givePermissionTo([
            'input-absensi',
            'edit-absensi',
            'view-absensi',
            'manage-poin-siswa',
            'view-poin',
            'view-laporan',
            'export-laporan',
        ]);

        // Siswa
        $siswa = Role::firstOrCreate(['name' => 'siswa']);
        $siswa->givePermissionTo([
            'view-absensi',
            'view-poin',
        ]);

        // Orang Tua
        $orangTua = Role::firstOrCreate(['name' => 'orang_tua']);
        $orangTua->givePermissionTo([
            'view-absensi',
            'view-poin',
        ]);

        // Kepala Sekolah (guru + manajemen sekolah)
        $kepalaSekolah = Role::firstOrCreate(['name' => 'kepala_sekolah']);
        $kepalaSekolah->givePermissionTo([
            'input-absensi',
            'edit-absensi',
            'view-absensi',
            'view-poin',
            'view-laporan',
            'export-laporan',
            'view-guru',
            'view-siswa',
            'view-kelas',
            'manage-guru',
            'manage-siswa',
            'manage-kelas',
            'manage-settings',
        ]);

        // Wakil Kepala Sekolah (guru + view data)
        $wakilKepalaSekolah = Role::firstOrCreate(['name' => 'wakil_kepala_sekolah']);
        $wakilKepalaSekolah->givePermissionTo([
            'input-absensi',
            'edit-absensi',
            'view-absensi',
            'view-poin',
            'view-laporan',
            'export-laporan',
            'view-guru',
            'view-siswa',
            'view-kelas',
        ]);

        // User biasa (default dari boilerplate)
        Role::firstOrCreate(['name' => 'user']);
    }
}