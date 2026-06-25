<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpkCategory extends Model
{
    protected $table = 'opk_categories';

    protected $fillable = ['nomor', 'nama', 'ikon', 'deskripsi'];

    public function laporans()
    {
        return $this->hasMany(OpkLaporan::class, 'kategori_id');
    }
}
