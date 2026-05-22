<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasRoles, Notifiable;

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

    public function instansi()
    {
        return $this->belongsTo(Instansi::class, 'instansi_id', 'id_instansi');
    }

    // Helper: dapat instansi dari user apapun rolenya
    public function getInstansi(): ?Instansi
    {
        // Admin punya instansi_id langsung di tabel users
        if ($this->instansi_id) {
            return Instansi::find($this->instansi_id);
        }
        if ($this->guru) {
            return $this->guru->instansi;
        }
        if ($this->siswa) {
            return $this->siswa->instansi;
        }
        if ($this->orangTua) {
            return $this->orangTua->siswa()->first()?->instansi;
        }

        return null;
    }
}
