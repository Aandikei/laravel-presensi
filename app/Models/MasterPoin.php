<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MasterPoin extends Model
{
    protected $table = 'master_poin';

    protected $primaryKey = 'id_poin';

    protected $fillable = [
        'instansi_id',
        'nama_pelanggaran',
        'deskripsi',
        'jumlah_poin',
    ];

    public function instansi()
    {
        return $this->belongsTo(Instansi::class, 'instansi_id', 'id_instansi');
    }

    public function logPoin()
    {
        return $this->hasMany(LogPoinSiswa::class, 'poin_id', 'id_poin');
    }

    public function getRouteKeyName(): string
    {
        return 'id_poin';
    }
}
