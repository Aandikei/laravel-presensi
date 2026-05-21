<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RekapBulanan extends Model
{
    protected $table = 'rekap_bulanan';
    protected $primaryKey = 'id_rekap';

    protected $fillable = [
        'reg_id',
        'bulan',
        'tahun',
        'hadir',
        'sakit',
        'izin',
        'alpa',
        'cabut',
        'terlambat',
        'poin_akumulasi',
    ];

    public function registrasi()
    {
        return $this->belongsTo(RegistrasiAkademik::class, 'reg_id', 'id_registrasi');
    }

    // Total pertemuan bulan ini
    public function getTotalPertemuanAttribute(): int
    {
        return $this->hadir + $this->sakit + $this->izin + $this->alpa + $this->cabut + $this->terlambat;
    }

    // Persentase kehadiran
    public function getPersentaseHadirAttribute(): float
    {
        $total = $this->total_pertemuan;
        if ($total === 0) return 0;
        return round(($this->hadir / $total) * 100, 1);
    }
}