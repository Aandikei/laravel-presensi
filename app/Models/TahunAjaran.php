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

    /**
     * Ambil semua kelas yang punya siswa terdaftar di tahun ajaran ini.
     */
    public function kelas()
    {
        return Kelas::whereHas('registrasiAkademik', fn ($q) => $q->where('tahun_id', $this->id_tahun));
    }

    public function registrasiAkademik()
    {
        return $this->hasMany(RegistrasiAkademik::class, 'tahun_id', 'id_tahun');
    }

    public function getRouteKeyName(): string
    {
        return 'id_tahun';
    }
}
