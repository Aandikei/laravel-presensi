<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Jadwal extends Model
{
    protected $table = 'jadwal';
    protected $primaryKey = 'id_jadwal';

    protected $fillable = [
        'kurikulum_id',
        'hari',
        'jam_mulai',
        'jam_selesai',
    ];

    public function kurikulum()
    {
        return $this->belongsTo(KurikulumKelas::class, 'kurikulum_id', 'id_kurikulum');
    }

    public function absensi()
    {
        return $this->hasMany(Absensi::class, 'jadwal_id', 'id_jadwal');
    }

    // Shortcut ke guru lewat kurikulum
    public function guru()
    {
        return $this->hasOneThrough(
            Guru::class,
            KurikulumKelas::class,
            'id_kurikulum',
            'id_guru',
            'kurikulum_id',
            'guru_id'
        );
    }
}