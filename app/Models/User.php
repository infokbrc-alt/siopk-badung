<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'users';

    protected $fillable = [
        'name', 'email', 'password', 'role',
        'nip', 'no_hp', 'instansi', 'is_active',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active'         => 'boolean',
    ];

    // ---- Role helpers ----
    public function isSuperAdmin(): bool  { return $this->role === 'superadmin'; }
    public function isAdmin(): bool       { return in_array($this->role, ['superadmin', 'admin']); }
    public function isVerifikator(): bool { return in_array($this->role, ['superadmin', 'admin', 'verifikator']); }
    public function isPetugas(): bool     { return $this->role === 'petugas'; }
    public function canVerify(): bool     { return $this->isVerifikator(); }

    // ---- Relasi ----
    public function verifikasiLaporans()
    {
        return $this->hasMany(OpkLaporan::class, 'diverifikasi_oleh');
    }
}
