<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TahunAjaran extends Model
{
    protected $table = 'tahun_ajaran';
    protected $primaryKey = 'id_tahun';

    protected $fillable = [
        'instansi_id',
        'nama_tahun',
        'semester',
        'tanggal_mulai',
        'tanggal_selesai',
        'is_aktif',
    ];

    protected $casts = [
        'is_aktif' => 'boolean',
        'tanggal_mulai' => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function instansi()
    {
        return $this->belongsTo(Instansi::class, 'instansi_id', 'id_instansi');
    }

    public function kelas()
    {
        return $this->hasMany(Kelas::class, 'tahun_id', 'id_tahun');
    }

    public function registrasiAkademik()
    {
        return $this->hasMany(RegistrasiAkademik::class, 'tahun_id', 'id_tahun');
    }
}