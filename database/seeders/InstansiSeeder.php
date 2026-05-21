<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Instansi;
use App\Models\TahunAjaran;

class InstansiSeeder extends Seeder
{
    public function run(): void
    {
        $instansi = Instansi::firstOrCreate(
            ['npsn' => '12345678'],
            [
                'nama_instansi' => 'SMA Negeri 1 Contoh',
                'jenjang'       => 'SMA',
                'alamat'        => 'Jl. Contoh No. 1, Kota Contoh',
                'telepon'       => '081234567890',
                'email'         => 'sman1contoh@example.com',
            ]
        );

        // Tahun ajaran aktif
        TahunAjaran::firstOrCreate(
            [
                'instansi_id' => $instansi->id_instansi,
                'nama_tahun'  => '2024/2025',
                'semester'    => 'Genap',
            ],
            [
                'tanggal_mulai'   => '2025-01-06',
                'tanggal_selesai' => '2025-06-30',
                'is_aktif'        => true,
            ]
        );
    }
}