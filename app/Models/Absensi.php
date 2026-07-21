<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Absensi extends Model
{
    protected $table = 'absensi';

    protected $primaryKey = 'id_absen';

    protected $fillable = [
        'reg_id',
        'jadwal_id',
        'tanggal',
        'status',
        'keterangan',
        'durasi_terlambat',
        'waktu_input',
        'is_locked',
        'cakupan',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'waktu_input' => 'datetime',
        'durasi_terlambat' => 'integer',
        'is_locked' => 'boolean',
        'cakupan' => 'string',
    ];

    public function registrasi()
    {
        return $this->belongsTo(RegistrasiAkademik::class, 'reg_id', 'id_registrasi');
    }

    public function jadwal()
    {
        return $this->belongsTo(Jadwal::class, 'jadwal_id', 'id_jadwal');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(User::class, 'updated_by', 'id');
    }

    public function logPoin()
    {
        return $this->hasOne(LogPoinSiswa::class, 'absen_id', 'id_absen');
    }

    // Scope untuk filter status
    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    // Scope belum dikunci
    public function scopeBelumDikunci($query)
    {
        return $query->where('is_locked', false);
    }

    // Warna badge per status
    public function getWarnaBadgeAttribute(): string
    {
        return match ($this->status) {
            'Hadir' => 'green',
            'Sakit' => 'blue',
            'Izin' => 'orange',
            'Alpa' => 'red',
            'Terlambat' => 'yellow',
            'Bolos' => 'pink',
            default => 'gray',
        };
    }

    public function getRouteKeyName(): string
    {
        return 'id_absen';
    }
}
