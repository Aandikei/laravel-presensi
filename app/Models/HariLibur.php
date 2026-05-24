<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HariLibur extends Model
{
    protected $table = 'hari_libur';

    protected $primaryKey = 'id_libur';

    protected $fillable = [
        'instansi_id',
        'tanggal',
        'nama_libur',
        'is_nasional',
    ];

    protected $casts = [
        'tanggal' => 'date',
        'is_nasional' => 'boolean',
    ];

    public function instansi()
    {
        return $this->belongsTo(Instansi::class, 'instansi_id', 'id_instansi');
    }

    // Cek apakah tanggal tertentu adalah hari libur
    public static function isLibur(string $tanggal, int $instansiId): bool
    {
        return self::where('tanggal', $tanggal)
            ->where('instansi_id', $instansiId)
            ->exists();
    }

    public static function getNamaLibur(string $tanggal, int $instansiId): ?string
    {
        return self::where('tanggal', $tanggal)
            ->where('instansi_id', $instansiId)
            ->value('nama_libur');
    }
}
