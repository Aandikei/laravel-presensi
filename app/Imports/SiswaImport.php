<?php

namespace App\Imports;

use App\Models\Kelas;
use App\Models\OrangTua;
use App\Models\OrtuSiswa;
use App\Models\RegistrasiAkademik;
use App\Models\Siswa;
use App\Models\User;
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
    protected $gagal = 0;
    protected $daftarUlang = 0;

    public function __construct(int $instansiId, ?int $tahunId = null)
    {
        $this->instansiId = $instansiId;
        $this->tahunId = $tahunId;
    }

    public function model(array $row)
    {
        $existingSiswa = Siswa::where('nisn', $row['nisn'])->with(['user', 'orangTua.user'])->first();

        if ($existingSiswa) {
            if ($existingSiswa->instansi_id !== $this->instansiId) {
                $hasActive = $existingSiswa->registrasiAkademik()
                    ->aktif()
                    ->whereHas('tahunAjaran', fn ($q) => $q->where('is_aktif', true))
                    ->whereHas('kelas', fn ($q) => $q->where('instansi_id', $existingSiswa->instansi_id))
                    ->exists();

                if (!$hasActive) {
                    return $this->prosesDaftarUlang($row, $existingSiswa);
                }
            }

            $this->gagal++;
            return null;
        }

        if (User::where('email', $row['email_siswa'])->exists()) {
            $this->gagal++;
            return null;
        }

        $userSiswa = User::create([
            'name' => $row['nama_siswa'],
            'email' => $row['email_siswa'],
            'password' => Hash::make($row['nisn']),
        ]);
        $userSiswa->assignRole('siswa');

        $siswa = Siswa::create([
            'user_id' => $userSiswa->id,
            'instansi_id' => $this->instansiId,
            'nisn' => $row['nisn'],
            'nama_siswa' => $row['nama_siswa'],
            'jenis_kelamin' => $row['jenis_kelamin'],
            'tanggal_lahir' => !empty($row['tanggal_lahir']) ? $row['tanggal_lahir'] : null,
        ]);

        if (!empty($row['email_ortu'])) {
            $userOrtu = User::where('email', $row['email_ortu'])->first();

            if (!$userOrtu) {
                $userOrtu = User::create([
                    'name' => $row['nama_ortu'] ?? 'Orang Tua ' . $row['nama_siswa'],
                    'email' => $row['email_ortu'],
                    'password' => Hash::make($row['nisn']),
                ]);
                $userOrtu->assignRole('orang_tua');
            }

            $ortu = OrangTua::firstOrCreate(
                ['user_id' => $userOrtu->id],
                [
                    'nama_ortu' => $row['nama_ortu'] ?? 'Orang Tua ' . $row['nama_siswa'],
                    'no_hp' => $row['no_hp_ortu'] ?? null,
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

        $tahunId = $this->tahunId;
        $kelasId = null;

        $kelas = null;
        if (!empty($row['nama_kelas'])) {
            $kelas = Kelas::where('instansi_id', $this->instansiId)
                ->where('nama_kelas', strtoupper($row['nama_kelas']))
                ->first();
            $kelasId = $kelas?->id_kelas;
        }

        if (!$kelas) {
            Log::warning('Kelas tidak ditemukan saat import', [
                'nama_kelas' => $row['nama_kelas'],
                'instansi_id' => $this->instansiId,
            ]);
            $this->kelasNotFound[] = $row['nama_kelas'];
        }

        if ($kelasId && $tahunId) {
            $sudahTerdaftar = RegistrasiAkademik::where('siswa_id', $siswa->id_siswa)
                ->where('tahun_id', $tahunId)
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

    private function prosesDaftarUlang(array $row, Siswa $existingSiswa): null
    {
        $oldUser = $existingSiswa->user;

        if ($row['email_siswa'] === $oldUser->email) {
            $oldUser->update([
                'name' => $existingSiswa->nama_siswa,
                'password' => Hash::make($row['nisn']),
            ]);
            $userSiswa = $oldUser;
        } else {
            if (User::where('email', $row['email_siswa'])->exists()) {
                $this->gagal++;
                return null;
            }

            $userSiswa = User::create([
                'name' => $existingSiswa->nama_siswa,
                'email' => $row['email_siswa'],
                'password' => Hash::make($row['nisn']),
            ]);
            $userSiswa->assignRole('siswa');
        }

        $existingSiswa->update([
            'user_id' => $userSiswa->id,
            'instansi_id' => $this->instansiId,
        ]);

        $tahunId = $this->tahunId;
        $kelasId = null;

        $kelas = null;
        if (!empty($row['nama_kelas'])) {
            $kelas = Kelas::where('instansi_id', $this->instansiId)
                ->where('nama_kelas', strtoupper($row['nama_kelas']))
                ->first();
            $kelasId = $kelas?->id_kelas;
        }

        if (!$kelas) {
            Log::warning('Kelas tidak ditemukan saat import daftar ulang', [
                'nama_kelas' => $row['nama_kelas'],
                'instansi_id' => $this->instansiId,
            ]);
            $this->kelasNotFound[] = $row['nama_kelas'];
        }

        if ($kelasId && $tahunId) {
            $sudahTerdaftar = RegistrasiAkademik::where('siswa_id', $existingSiswa->id_siswa)
                ->where('tahun_id', $tahunId)
                ->exists();

            if (!$sudahTerdaftar) {
                RegistrasiAkademik::create([
                    'siswa_id' => $existingSiswa->id_siswa,
                    'kelas_id' => $kelasId,
                    'tahun_id' => $tahunId,
                ]);
            }
        }

        $this->daftarUlang++;
        return null;
    }

    public function getKelasNotFound(): array { return array_unique($this->kelasNotFound); }
    public function getBerhasil(): int { return $this->berhasil; }
    public function getGagal(): int { return $this->gagal; }
    public function getDaftarUlang(): int { return $this->daftarUlang; }
}
