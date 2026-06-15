<?php

namespace App\Models;

use App\Observers\OrangTuaObserver;
use Illuminate\Database\Eloquent\Model;

class OrangTua extends Model
{
    protected $table = 'orang_tua';
    protected $primaryKey = 'id_ortu';

    protected $fillable = [
        'user_id',
        'nama_ortu',
        'no_hp',
    ];

    protected static function booted(): void
    {
        static::observe(OrangTuaObserver::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function siswa()
    {
        return $this->belongsToMany(
            Siswa::class,
            'ortu_siswa',
            'ortu_id',
            'siswa_id',
            'id_ortu',
            'id_siswa'
        )->withPivot('hubungan', 'is_primary')->withTimestamps();
    }
}