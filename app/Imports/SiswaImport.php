<?php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\User;
use App\Models\OrangTua;
use App\Models\OrtuSiswa;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\WithSkipDuplicates;

class SiswaImport implements ToModel, WithHeadingRow, SkipsOnError
{
    use SkipsErrors;

    protected $instansiId;
    protected $berhasil = 0;
    protected $gagal    = 0;

    public function __construct(int $instansiId)
    {
        $this->instansiId = $instansiId;
    }

    public function model(array $row)
    {
        // Skip kalau NISN sudah ada
        if (Siswa::where('nisn', $row['nisn'])->exists()) {
            $this->gagal++;
            return null;
        }

        // Skip kalau email sudah ada
        if (User::where('email', $row['email_siswa'])->exists()) {
            $this->gagal++;
            return null;
        }

        // Buat user siswa
        $userSiswa = User::create([
            'name'     => $row['nama_siswa'],
            'email'    => $row['email_siswa'],
            'password' => Hash::make($row['nisn']), // default password = nisn
        ]);
        $userSiswa->assignRole('siswa');

        // Buat siswa
        $siswa = Siswa::create([
            'user_id'       => $userSiswa->id,
            'instansi_id'   => $this->instansiId,
            'nisn'          => $row['nisn'],
            'nama_siswa'    => $row['nama_siswa'],
            'jenis_kelamin' => $row['jenis_kelamin'],
            'tanggal_lahir' => $row['tanggal_lahir'] ?? null,
        ]);

        // Buat orang tua kalau ada
        if (!empty($row['email_ortu'])) {
            $userOrtu = User::where('email', $row['email_ortu'])->first();

            if (!$userOrtu) {
                $userOrtu = User::create([
                    'name'     => $row['nama_ortu'] ?? 'Orang Tua ' . $row['nama_siswa'],
                    'email'    => $row['email_ortu'],
                    'password' => Hash::make($row['nisn']),
                ]);
                $userOrtu->assignRole('orang_tua');
            }

            $ortu = OrangTua::firstOrCreate(
                ['user_id' => $userOrtu->id],
                ['nama_ortu' => $row['nama_ortu'] ?? 'Orang Tua ' . $row['nama_siswa']]
            );

            OrtuSiswa::firstOrCreate(
                ['ortu_id' => $ortu->id_ortu, 'siswa_id' => $siswa->id_siswa],
                ['hubungan' => $row['hubungan'] ?? 'Wali', 'is_primary' => true]
            );
        }

        $this->berhasil++;
        return null; // return null karena sudah handle manual
    }

    public function getBerhasil(): int { return $this->berhasil; }
    public function getGagal(): int { return $this->gagal; }
}