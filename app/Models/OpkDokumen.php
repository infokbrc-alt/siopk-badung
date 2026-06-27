<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpkDokumen extends Model
{
    protected $table = 'opk_dokumens';

    protected $fillable = [
        'laporan_id', 'nama_file', 'path', 'judul', 'jenis', 'ukuran_bytes',
    ];

    public function laporan()
    {
        return $this->belongsTo(OpkLaporan::class, 'laporan_id');
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/'.$this->path);
    }
}
