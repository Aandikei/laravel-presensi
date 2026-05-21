<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Instansi;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\OrangTua;
use App\Models\OrtuSiswa;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $instansi = Instansi::first();

        // Super Admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@app.com'],
            ['name' => 'Super Admin', 'password' => Hash::make('password')]
        );
        $superAdmin->assignRole('super_admin');

        // Admin Sekolah
        $admin = User::firstOrCreate(
            ['email' => 'admin@app.com'],
            ['name' => 'Admin Sekolah', 'password' => Hash::make('password')]
        );
        $admin->assignRole('admin');

        // Guru Biasa
        $userGuru = User::firstOrCreate(
            ['email' => 'guru@app.com'],
            ['name' => 'Budi Santoso', 'password' => Hash::make('password')]
        );
        $userGuru->assignRole('guru');
        Guru::firstOrCreate(
            ['user_id' => $userGuru->id],
            [
                'instansi_id'   => $instansi->id_instansi,
                'nip'           => '198501012010011001',
                'nama_guru'     => 'Budi Santoso',
                'jenis_kelamin' => 'L',
                'no_hp'         => '081111111111',
            ]
        );

        // Wali Kelas
        $userWali = User::firstOrCreate(
            ['email' => 'walikelas@app.com'],
            ['name' => 'Siti Rahayu', 'password' => Hash::make('password')]
        );
        $userWali->assignRole('wali_kelas');
        Guru::firstOrCreate(
            ['user_id' => $userWali->id],
            [
                'instansi_id'   => $instansi->id_instansi,
                'nip'           => '198701012010012002',
                'nama_guru'     => 'Siti Rahayu',
                'jenis_kelamin' => 'P',
                'no_hp'         => '082222222222',
            ]
        );

        // Siswa
        $userSiswa = User::firstOrCreate(
            ['email' => 'siswa@app.com'],
            ['name' => 'Ahmad Fauzi', 'password' => Hash::make('password')]
        );
        $userSiswa->assignRole('siswa');
        $siswa = Siswa::firstOrCreate(
            ['user_id' => $userSiswa->id],
            [
                'instansi_id'   => $instansi->id_instansi,
                'nisn'          => '0012345678',
                'nama_siswa'    => 'Ahmad Fauzi',
                'jenis_kelamin' => 'L',
                'tanggal_lahir' => '2007-05-15',
            ]
        );

        // Orang Tua
        $userOrtu = User::firstOrCreate(
            ['email' => 'orangtua@app.com'],
            ['name' => 'Fauzi Senior', 'password' => Hash::make('password')]
        );
        $userOrtu->assignRole('orang_tua');
        $ortu = OrangTua::firstOrCreate(
            ['user_id' => $userOrtu->id],
            [
                'nama_ortu' => 'Fauzi Senior',
                'no_hp'     => '083333333333',
            ]
        );

        // Link ortu ke siswa
        OrtuSiswa::firstOrCreate(
            [
                'ortu_id'  => $ortu->id_ortu,
                'siswa_id' => $siswa->id_siswa,
            ],
            [
                'hubungan'   => 'Ayah',
                'is_primary' => true,
            ]
        );
    }
}