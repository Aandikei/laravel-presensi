<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Guru extends Model
{
    use SoftDeletes;

    protected $table = 'guru';
    protected $primaryKey = 'id_guru';

    protected $fillable = [
        'user_id',
        'instansi_id',
        'nip',
        'nama_guru',
        'jenis_kelamin',
        'no_hp',
        'foto',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function instansi()
    {
        return $this->belongsTo(Instansi::class, 'instansi_id', 'id_instansi');
    }

    public function kelasWali()
    {
        return $this->hasMany(Kelas::class, 'guru_wali_id', 'id_guru');
    }

    public function kurikulum()
    {
        return $this->hasMany(KurikulumKelas::class, 'guru_id', 'id_guru');
    }

    public function jadwal()
    {
        return $this->hasManyThrough(
            Jadwal::class,
            KurikulumKelas::class,
            'guru_id',
            'kurikulum_id',
            'id_guru',
            'id_kurikulum'
        );
    }

    // Cek apakah guru adalah wali kelas
    public function isWaliKelas(): bool
    {
        return $this->kelasWali()->exists();
    }

    public function getRouteKeyName(): string 
    {
        return 'id_guru';
    }
}