<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Guru extends Model
{
    use SoftDeletes;

    protected $table = 'guru';
    protected $primaryKey = 'id_guru';

    protected $fillable = [
        'user_id',
        'instansi_id',
        'instansi_tujuan_id',
        'transfer_token',
        'transfer_token_expires_at',
        'nip',
        'nama_guru',
        'jenis_kelamin',
        'no_hp',
        'foto',
        'status',
    ];

    protected $casts = [
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

    public function instansiTujuan()
    {
        return $this->belongsTo(Instansi::class, 'instansi_tujuan_id', 'id_instansi');
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

    // === Mutasi / Transfer ===

    public function generateTransferToken(): void
    {
        $this->update([
            'transfer_token' => strtoupper(Str::random(6)),
            'transfer_token_expires_at' => now()->addDays(7),
        ]);
    }

    public function clearTransferToken(): void
    {
        $this->update([
            'transfer_token' => null,
            'transfer_token_expires_at' => null,
            'instansi_tujuan_id' => null,
        ]);
    }

    public function isTransferTokenExpired(): bool
    {
        return $this->transfer_token_expires_at && $this->transfer_token_expires_at->isPast();
    }

    // === Status ===

    public function isAktif(): bool
    {
        return is_null($this->status);
    }

    public function markAsKeluar(): void
    {
        $this->update(['status' => 'Keluar']);
    }

    public function markAsPensiun(): void
    {
        $this->update(['status' => 'Pensiun']);
    }

    public function getStatusLabelAttribute(): ?string
    {
        return match ($this->status) {
            'Keluar' => 'Keluar',
            'Pensiun' => 'Pensiun',
            default => null,
        };
    }
}