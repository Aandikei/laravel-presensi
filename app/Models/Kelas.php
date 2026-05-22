<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kelas extends Model
{
    protected $table = 'kelas';
    protected $primaryKey = 'id_kelas';

    protected $fillable = [
        'instansi_id',
        'tahun_id',
        'guru_wali_id',
        'nama_kelas',
        'tingkat',
    ];

    public function instansi()
    {
        return $this->belongsTo(Instansi::class, 'instansi_id', 'id_instansi');
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_id', 'id_tahun');
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

    // Jumlah siswa di kelas ini
    public function getJumlahSiswaAttribute(): int
    {
        return $this->registrasiAkademik()->count();
    }

    public function getRouteKeyName(): string
    {
        return 'id_kelas';
    }
}