<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrtuSiswa extends Model
{
    protected $table = 'ortu_siswa';

    protected $fillable = [
        'ortu_id',
        'siswa_id',
        'hubungan',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function orangTua()
    {
        return $this->belongsTo(OrangTua::class, 'ortu_id', 'id_ortu');
    }

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id', 'id_siswa');
    }
}