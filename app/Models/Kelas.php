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
    ];

    public function instansi()
    {
        return $this->belongsTo(Instansi::class, 'instansi_id', 'id_instansi');
    }

    /**
     * Tingkat maksimum berdasarkan jenjang instansi.
     * SD = 6, SMP = 9, SMA/SMK = 12
     */
    public function getTingkatMaksAttribute(): int
    {
        return $this->instansi->tingkat_maks ?? 12;
    }

    public function waliKelas()
    {
        return $this->belongsTo(Guru::class, 'guru_wali_id', 'id_guru');
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