<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Instansi extends Model
{
    use SoftDeletes;

    protected $table = 'instansi';
    protected $primaryKey = 'id_instansi';

    protected $fillable = [
        'nama_instansi',
        'jenjang',
        'npsn',
        'alamat',
        'telepon',
        'email',
        'logo',
    ];

    // Relasi
    public function tahunAjaran()
    {
        return $this->hasMany(TahunAjaran::class, 'instansi_id', 'id_instansi');
    }

    public function tahunAktif()
    {
        return $this->hasOne(TahunAjaran::class, 'instansi_id', 'id_instansi')
            ->where('is_aktif', true);
    }

    public function guru()
    {
        return $this->hasMany(Guru::class, 'instansi_id', 'id_instansi');
    }

    public function siswa()
    {
        return $this->hasMany(Siswa::class, 'instansi_id', 'id_instansi');
    }

    public function kelas()
    {
        return $this->hasMany(Kelas::class, 'instansi_id', 'id_instansi');
    }

    public function mataPelajaran()
    {
        return $this->hasMany(MataPelajaran::class, 'instansi_id', 'id_instansi');
    }

    public function masterPoin()
    {
        return $this->hasMany(MasterPoin::class, 'instansi_id', 'id_instansi');
    }
}