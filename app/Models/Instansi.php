<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Instansi extends Model
{
    protected $table = 'instansi';
    protected $primaryKey = 'id_instansi';

    protected $fillable = [
        'nama_instansi',
        'jenjang',
        'label_jenjang',
        'npsn',
        'alamat',
        'telepon',
        'email',
    ];

    public function getLabelJenjangAttribute()
    {
        return $this->attributes['label_jenjang'] ?? $this->attributes['jenjang'];
    }

    // Relasi
    public function tahunAjaran()
    {
        return $this->hasMany(TahunAjaran::class, 'instansi_id', 'id_instansi');
    }

    public function tahunAktif()
    {
        return $this->hasOne(TahunAjaran::class, 'instansi_id', 'id_instansi')
            ->where('is_aktif', true);
    }

    public function guru()
    {
        return $this->hasMany(Guru::class, 'instansi_id', 'id_instansi');
    }

    public function siswa()
    {
        return $this->hasMany(Siswa::class, 'instansi_id', 'id_instansi');
    }

    public function kelas()
    {
        return $this->hasMany(Kelas::class, 'instansi_id', 'id_instansi');
    }

    public function mataPelajaran()
    {
        return $this->hasMany(MataPelajaran::class, 'instansi_id', 'id_instansi');
    }

    public function masterPoin()
    {
        return $this->hasMany(MasterPoin::class, 'instansi_id', 'id_instansi');
    }

    /**
     * Tingkat maksimum berdasarkan jenjang.
     * SD = 6, SMP = 9, SMA/SMK = 12
     */
    public function getTingkatMaksAttribute(): int
    {
        return match ($this->jenjang) {
            'SD'  => 6,
            'SMP' => 9,
            'SMA', 'SMK' => 12,
            default => 12,
        };
    }

    /**
     * Tingkat minimum berdasarkan jenjang.
     * SD = 1, SMP = 7, SMA/SMK = 10
     */
    public function getTingkatMinAttribute(): int
    {
        return match ($this->jenjang) {
            'SD'  => 1,
            'SMP' => 7,
            'SMA', 'SMK' => 10,
            default => 1,
        };
    }
}