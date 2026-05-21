<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use Notifiable, HasRoles;

    protected $fillable = ['name', 'email', 'password'];

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

    // Helper: dapat instansi dari user apapun rolenya
    public function getInstansi()
    {
        if ($this->guru) return $this->guru->instansi;
        if ($this->siswa) return $this->siswa->instansi;
        if ($this->orangTua) {
            return $this->orangTua->siswa()->first()?->instansi;
        }
        return null;
    }
}