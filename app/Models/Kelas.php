<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    protected $table = 'kelas';
    protected $primaryKey = 'id_kelas';

    protected $fillable = [
        'instansi_id',
        'guru_wali_id',
        'nama_kelas',
        'tingkat',
        'jurusan_id',
        'nomor_kelas',
    ];

    protected static function booted(): void
    {
        static::saving(function (Kelas $kelas) {
            $kelas->generateNamaKelas();
        });
    }

    public function generateNamaKelas(): void
    {
        $roman = $this->tingkat_roman;

        if ($this->jurusan_id) {
            $kode = $this->jurusan?->kode_jurusan ?? '';
            $this->nama_kelas = trim($roman . ' ' . $kode . ($this->nomor_kelas ? ' ' . $this->nomor_kelas : ''));
        } else {
            $this->nama_kelas = trim($roman . ($this->nomor_kelas ? ' ' . $this->nomor_kelas : ''));
        }
    }

    public function instansi()
    {
        return $this->belongsTo(Instansi::class, 'instansi_id', 'id_instansi');
    }

    /**
     * Tingkat maksimum berdasarkan jenjang instansi.
     * SD = 6, SMP = 9, SMA = 12
     */
    public function getTingkatMaksAttribute(): int
    {
        return $this->instansi->tingkat_maks ?? 12;
    }

    public function getTingkatRomanAttribute(): string
    {
        $map = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];
        return $map[$this->tingkat] ?? (string) $this->tingkat;
    }

    public function waliKelas()
    {
        return $this->belongsTo(Guru::class, 'guru_wali_id', 'id_guru');
    }

    public function jurusan()
    {
        return $this->belongsTo(Jurusan::class, 'jurusan_id', 'id_jurusan');
    }

    public function kurikulum()
    {
        return $this->hasMany(KurikulumKelas::class, 'kelas_id', 'id_kelas');
    }

    public function registrasiAkademik()
    {
        return $this->hasMany(RegistrasiAkademik::class, 'kelas_id', 'id_kelas');
    }

    public function siswa()
    {
        return $this->hasManyThrough(
            Siswa::class,
            RegistrasiAkademik::class,
            'kelas_id',
            'id_siswa',
            'id_kelas',
            'siswa_id'
        );
    }

    public function jadwal()
    {
        return $this->hasManyThrough(
            Jadwal::class,
            KurikulumKelas::class,
            'kelas_id',
            'kurikulum_id',
            'id_kelas',
            'id_kurikulum'
        );
    }

    // Jumlah siswa aktif di kelas ini
    public function getJumlahSiswaAttribute(): int
    {
        if (isset($this->attributes['jumlah_siswa'])) {
            return (int) $this->attributes['jumlah_siswa'];
        }

        $tahunAktif = TahunAjaran::where('instansi_id', $this->instansi_id)
            ->where('is_aktif', true)
            ->first();

        return $this->registrasiAkademik()
            ->aktif()
            ->when($tahunAktif, fn($q) => $q->where('tahun_id', $tahunAktif->id_tahun))
            ->count();
    }

    public function getRouteKeyName(): string
    {
        return 'id_kelas';
    }
}