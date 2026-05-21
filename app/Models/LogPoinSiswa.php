<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LogPoinSiswa extends Model
{
    protected $table = 'log_poin_siswa';
    protected $primaryKey = 'id_log_poin';

    protected $fillable = [
        'siswa_id',
        'absen_id',
        'poin_id',
        'tanggal',
        'keterangan',
        'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function siswa()
    {
        return $this->belongsTo(Siswa::class, 'siswa_id', 'id_siswa');
    }

    public function absensi()
    {
        return $this->belongsTo(Absensi::class, 'absen_id', 'id_absen');
    }

    public function masterPoin()
    {
        return $this->belongsTo(MasterPoin::class, 'poin_id', 'id_poin');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}