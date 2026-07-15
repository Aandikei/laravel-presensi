<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jurusan extends Model
{
    protected $table = 'jurusan';
    protected $primaryKey = 'id_jurusan';

    protected $fillable = [
        'instansi_id',
        'kode_jurusan',
        'nama_jurusan',
    ];

    public function instansi()
    {
        return $this->belongsTo(Instansi::class, 'instansi_id', 'id_instansi');
    }

    public function kelas()
    {
        return $this->hasMany(Kelas::class, 'jurusan_id', 'id_jurusan');
    }
}
