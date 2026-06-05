<?php

namespace App\Imports;

use App\Models\Siswa;
use App\Models\User;
use App\Models\OrangTua;
use App\Models\OrtuSiswa;
use App\Models\Kelas;
use App\Models\TahunAjaran;
use App\Models\RegistrasiAkademik;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;

class SiswaImport implements ToModel, WithHeadingRow, SkipsOnError
{
    use SkipsErrors;

    protected $instansiId;
    protected $tahunId;
    protected $kelasNotFound = [];
    protected $berhasil = 0;
    protected $gagal    = 0;

    public function __construct(int $instansiId, ?int $tahunId = null)
    {
        $this->instansiId = $instansiId;
        $this->tahunId    = $tahunId;
    }

    public function model(array $row)
    {
        // Skip kalau NISN sudah ada
        if (Siswa::where('nisn', '=', $row['nisn'])->exists()) {
            $this->gagal++;
            return null;
        }

        // Skip kalau email sudah ada
        if (User::where('email', '=', $row['email_siswa'])->exists()) {
            $this->gagal++;
            return null;
        }

        // Buat user siswa
        $userSiswa = User::create([
            'name'     => $row['nama_siswa'],
            'email'    => $row['email_siswa'],
            'password' => Hash::make($row['nisn']),
        ]);
        $userSiswa->assignRole('siswa');

        // Buat siswa
        $siswa = Siswa::create([
            'user_id'       => $userSiswa->id,
            'instansi_id'   => $this->instansiId,
            'nisn'          => $row['nisn'],
            'nama_siswa'    => $row['nama_siswa'],
            'jenis_kelamin' => $row['jenis_kelamin'],
            'tanggal_lahir' => !empty($row['tanggal_lahir']) ? $row['tanggal_lahir'] : null,
        ]);

        // Buat orang tua
        if (!empty($row['email_ortu'])) {
            $userOrtu = User::where('email', '=', $row['email_ortu'])->first();

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
                [
                    'nama_ortu' => $row['nama_ortu'] ?? 'Orang Tua ' . $row['nama_siswa'],
                    'no_hp'     => $row['no_hp_ortu'] ?? null,
                ]
            );

            if (!$userOrtu->hasRole('orang_tua')) {
                $userOrtu->assignRole('orang_tua');
            }

            OrtuSiswa::firstOrCreate(
                ['ortu_id' => $ortu->id_ortu, 'siswa_id' => $siswa->id_siswa],
                ['hubungan' => $row['hubungan'] ?? 'Wali', 'is_primary' => true]
            );
        }

        // Registrasi ke kelas
        $tahunId  = $this->tahunId;
        $kelasId  = null;

        // Cari kelas berdasarkan nama di kolom excel (kalau ada)
        $kelas = null;
        if (!empty($row['nama_kelas'])) {
            $kelas = Kelas::where('instansi_id', '=', $this->instansiId)
                ->where('nama_kelas', '=', $row['nama_kelas'])
                ->first();
            $kelasId = $kelas?->id_kelas;
        }

        if (!$kelas) {
            Log::warning('Kelas tidak ditemukan saat import', [
                'nama_kelas'  => $row['nama_kelas'],
                'instansi_id' => $this->instansiId,
            ]);
            $this->kelasNotFound[] = $row['nama_kelas'];
        }

        if ($kelasId && $tahunId) {
            $sudahTerdaftar = RegistrasiAkademik::where('siswa_id', '=', $siswa->id_siswa)
                ->where('tahun_id', '=', $tahunId)
                ->exists();

            if (!$sudahTerdaftar) {
                RegistrasiAkademik::create([
                    'siswa_id' => $siswa->id_siswa,
                    'kelas_id' => $kelasId,
                    'tahun_id' => $tahunId,
                ]);
            }
        }

        $this->berhasil++;
        return null;
    }

    public function getKelasNotFound(): array { return array_unique($this->kelasNotFound); }
    public function getBerhasil(): int { return $this->berhasil; }
    public function getGagal(): int { return $this->gagal; }
}