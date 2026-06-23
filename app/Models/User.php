<?php

namespace App\Models;

use Illuminate\Auth\MustVerifyEmail as MustVerifyEmailTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements MustVerifyEmailContract
{
    use HasRoles, Notifiable, MustVerifyEmailTrait;

    protected $fillable = ['name', 'email', 'password', 'instansi_id'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function guru()
    {
        return $this->hasOne(Guru::class, 'user_id', 'id');
    }

    public function siswa()
    {
        return $this->hasOne(Siswa::class, 'user_id', 'id');
    }

    public function orangTua()
    {
        return $this->hasOne(OrangTua::class, 'user_id', 'id');
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\VerifyEmail);
    }

    public function instansi()
    {
        return $this->belongsTo(Instansi::class, 'instansi_id', 'id_instansi');
    }

    private ?Instansi $cachedInstansi = null;

    public function getInstansi(): ?Instansi
    {
        if ($this->cachedInstansi) {
            return $this->cachedInstansi;
        }

        if ($this->instansi_id) {
            $this->cachedInstansi = Instansi::find($this->instansi_id);
        } elseif ($this->relationLoaded('guru') && $this->guru) {
            $this->cachedInstansi = $this->guru->instansi;
        } elseif ($this->relationLoaded('siswa') && $this->siswa) {
            $this->cachedInstansi = $this->siswa->instansi;
        } elseif ($this->relationLoaded('orangTua') && $this->orangTua) {
            $this->cachedInstansi = $this->orangTua->siswa()->first()?->instansi;
        } else {
            if ($this->guru) {
                $this->cachedInstansi = $this->guru->instansi;
            } elseif ($this->siswa) {
                $this->cachedInstansi = $this->siswa->instansi;
            } elseif ($this->orangTua) {
                $this->cachedInstansi = $this->orangTua->siswa()->first()?->instansi;
            }
        }

        return $this->cachedInstansi;
    }
}
