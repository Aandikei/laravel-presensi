<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Siswa extends Model
{
    use SoftDeletes;

    protected $table = 'siswa';

    protected $primaryKey = 'id_siswa';

    protected $fillable = [
        'user_id',
        'instansi_id',
        'nisn',
        'nama_siswa',
        'jenis_kelamin',
        'tanggal_lahir',
        'foto',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function instansi()
    {
        return $this->belongsTo(Instansi::class, 'instansi_id', 'id_instansi');
    }

    public function orangTua()
    {
        return $this->belongsToMany(
            OrangTua::class,
            'ortu_siswa',
            'siswa_id',
            'ortu_id',
            'id_siswa',
            'id_ortu'
        )->withPivot('hubungan', 'is_primary')->withTimestamps();
    }

    public function registrasiAkademik()
    {
        return $this->hasMany(RegistrasiAkademik::class, 'siswa_id', 'id_siswa');
    }

    public function registrasiAktif()
    {
        return $this->hasOne(RegistrasiAkademik::class, 'siswa_id', 'id_siswa')
            ->whereHas('tahunAjaran', fn ($q) => $q->where('is_aktif', true));
    }

    public function absensi()
    {
        return $this->hasManyThrough(
            Absensi::class,
            RegistrasiAkademik::class,
            'siswa_id',
            'reg_id',
            'id_siswa',
            'id_registrasi'
        );
    }

    public function logPoin()
    {
        return $this->hasMany(LogPoinSiswa::class, 'siswa_id', 'id_siswa');
    }

    public function rekapBulanan()
    {
        return $this->hasManyThrough(
            RekapBulanan::class,
            RegistrasiAkademik::class,
            'siswa_id',
            'reg_id',
            'id_siswa',
            'id_registrasi'
        );
    }

    // Total poin akumulasi
    // public function getTotalPoinAttribute(): int
    // {
    //     return $this->logPoin()->sum('jumlah_poin') ?? 0;
    // }
    public function getTotalPoinAttribute(): int
    {
        return $this->logPoin()
            ->join('master_poin', 'log_poin_siswa.poin_id', '=', 'master_poin.id_poin')
            ->sum('master_poin.jumlah_poin') ?? 0;
    }
}
