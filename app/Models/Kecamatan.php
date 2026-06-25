<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kecamatan extends Model
{
    protected $table = 'kecamatans';
    protected $fillable = ['nama', 'kode'];

    public function desaDinas()
    {
        return $this->hasMany(DesaDinas::class, 'kecamatan_id');
    }

    public function desaAdats()
    {
        return $this->hasMany(DesaAdat::class, 'kecamatan_id');
    }

    public function laporans()
    {
        return $this->hasMany(OpkLaporan::class, 'kecamatan_id');
    }
}
