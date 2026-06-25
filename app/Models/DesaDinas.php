<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DesaDinas extends Model
{
    protected $table = 'desa_dinas';
    protected $fillable = ['kecamatan_id', 'nama', 'kode'];

    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_id');
    }

    public function laporans()
    {
        return $this->hasMany(OpkLaporan::class, 'desa_dinas_id');
    }
}
