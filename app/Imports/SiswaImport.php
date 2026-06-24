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
    protected $gagalList = [];

    public function __construct(int $instansiId, ?int $tahunId = null)
    {
        $this->instansiId = $instansiId;
        $this->tahunId = $tahunId;
    }

    public function model(array $row)
    {
        $existingSiswa = Siswa::where('nisn', $row['nisn'])->with(['user', 'orangTua.user', 'instansi'])->first();

        if ($existingSiswa) {
            if ($existingSiswa->instansi_id !== $this->instansiId) {
                $bisaDaftarUlang = $existingSiswa->registrasiAkademik()
                    ->whereIn('status', ['Alumni', 'Keluar'])
                    ->exists();

                if (!$bisaDaftarUlang) {
                    $this->gagal++;
                    $this->gagalList[] = [
                        'nama' => $existingSiswa->nama_siswa,
                        'nisn' => $row['nisn'],
                        'alasan' => 'Masih terdaftar di ' . $existingSiswa->instansi->nama_instansi,
                    ];
                    return null;
                }

                return $this->prosesDaftarUlang($row, $existingSiswa);
            }

            $this->gagal++;
            $this->gagalList[] = [
                'nama' => $existingSiswa->nama_siswa,
                'nisn' => $row['nisn'],
                'alasan' => 'NISN sudah terdaftar di sekolah ini',
            ];
            return null;
        }

        if (User::where('email', $row['email_siswa'])->exists()) {
            $this->gagal++;
            $this->gagalList[] = [
                'nama' => $row['nama_siswa'],
                'nisn' => $row['nisn'],
                'alasan' => 'Email siswa sudah digunakan',
            ];
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
                $this->gagalList[] = [
                    'nama' => $existingSiswa->nama_siswa,
                    'nisn' => $row['nisn'],
                    'alasan' => 'Email siswa sudah digunakan',
                ];
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

        $existingSiswa->logPoin()->delete();

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

        if (!empty($row['email_ortu'])) {
            $emailSama = $existingSiswa->orangTua->contains(
                fn($ortu) => $ortu->user->email === $row['email_ortu']
            );

            if (!$emailSama) {
                foreach ($existingSiswa->orangTua as $ortu) {
                    OrtuSiswa::where('siswa_id', $existingSiswa->id_siswa)
                        ->where('ortu_id', $ortu->id_ortu)->delete();

                    if ($ortu->siswa()->count() === 0) {
                        $ortu->delete();
                    }
                }

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
                    ['ortu_id' => $ortu->id_ortu, 'siswa_id' => $existingSiswa->id_siswa],
                    ['hubungan' => $row['hubungan'] ?? 'Wali', 'is_primary' => true]
                );
            }
        }

        $this->daftarUlang++;
        return null;
    }

    public function getKelasNotFound(): array { return array_unique($this->kelasNotFound); }
    public function getBerhasil(): int { return $this->berhasil; }
    public function getGagal(): int { return $this->gagal; }
    public function getDaftarUlang(): int { return $this->daftarUlang; }
    public function getGagalList(): array { return $this->gagalList; }
}
