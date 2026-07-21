<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KurikulumKelas extends Model
{
    protected $table = 'kurikulum_kelas';

    protected $primaryKey = 'id_kurikulum';

    protected $fillable = [
        'kelas_id',
        'mapel_id',
        'guru_id',
        'jenis_pengajar',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class, 'kelas_id', 'id_kelas');
    }

    public function mataPelajaran()
    {
        return $this->belongsTo(MataPelajaran::class, 'mapel_id', 'id_mapel');
    }

    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id', 'id_guru');
    }

    public function jadwal()
    {
        return $this->hasMany(Jadwal::class, 'kurikulum_id', 'id_kurikulum');
    }

    public function getRouteKeyName(): string
    {
        return 'id_kurikulum';
    }
}
