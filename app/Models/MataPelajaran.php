<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MataPelajaran extends Model
{
    protected $table = 'mata_pelajaran';

    protected $primaryKey = 'id_mapel';

    protected $fillable = [
        'instansi_id',
        'nama_mapel',
        'kode_mapel',
        'kelompok',
    ];

    public function instansi()
    {
        return $this->belongsTo(Instansi::class, 'instansi_id', 'id_instansi');
    }

    public function kurikulum()
    {
        return $this->hasMany(KurikulumKelas::class, 'mapel_id', 'id_mapel');
    }

    public function getRouteKeyName(): string
    {
        return 'id_mapel';
    }
}
