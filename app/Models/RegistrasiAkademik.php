<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RegistrasiAkademik extends Model
{
    protected $table = 'registrasi_akademik';
    protected $primaryKey = 'id_registrasi';

    protected $fillable = [
        'siswa_id',
        'kelas_id',
        'tahun_id',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id', 'id_siswa');
    }

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id', 'id_kelas');
    }

    public function tahunAjaran()
    {
        return $this->belongsTo(TahunAjaran::class, 'tahun_id', 'id_tahun');
    }

    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'reg_id', 'id_registrasi');
    }

    public function rekapBulanan()
    {
        return $this->hasMany(RekapBulanan::class, 'reg_id', 'id_registrasi');
    }
}