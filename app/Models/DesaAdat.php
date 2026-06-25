<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DesaAdat extends Model
{
    protected $table = 'desa_adats';
    protected $fillable = ['kecamatan_id', 'nama', 'banjar_adat'];

    public function kecamatan()
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_id');
    }
}
