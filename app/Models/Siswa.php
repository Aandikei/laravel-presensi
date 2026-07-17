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
        'asal_instansi_id',
        'nisn',
        'nama_siswa',
        'jenis_kelamin',
        'tanggal_lahir',
        'foto',
        'status',
        'transfer_token',
        'transfer_token_expires_at',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'transfer_token_expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

    public function instansi()
    {
        return $this->belongsTo(Instansi::class, 'instansi_id', 'id_instansi');
    }

    public function asalInstansi()
    {
        return $this->belongsTo(Instansi::class, 'asal_instansi_id', 'id_instansi');
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
            ->aktif()
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

    public function isAktif(): bool
    {
        return is_null($this->status);
    }

    public function markAsKeluar(): void
    {
        $this->update(['status' => 'Keluar']);
    }

    public function scopeAktif($query)
    {
        return $query->whereNull('status');
    }

    public function getStatusLabelAttribute(): ?string
    {
        return match ($this->status) {
            'Keluar' => 'Keluar',
            default => null,
        };
    }

    public function getRouteKeyName(): string
    {
        return 'id_siswa';
    }

    public function generateTransferToken(int $expiresInDays = 7): string
    {
        $token = strtoupper(substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 6));
        $this->update([
            'transfer_token' => $token,
            'transfer_token_expires_at' => now()->addDays($expiresInDays),
        ]);

        return $token;
    }

    public function clearTransferToken(): void
    {
        $this->update([
            'transfer_token' => null,
            'transfer_token_expires_at' => null,
        ]);
    }

    public function isTransferTokenExpired(): bool
    {
        return $this->transfer_token_expires_at && $this->transfer_token_expires_at->isPast();
    }

    public function isMutasi(): bool
    {
        return !is_null($this->asal_instansi_id) && $this->instansi_id !== $this->asal_instansi_id;
    }

    public function clearAsalInstansi(): void
    {
        $this->update(['asal_instansi_id' => null]);
    }
}
