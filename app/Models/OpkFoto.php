<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpkFoto extends Model
{
    protected $table = 'opk_fotos';

    protected $fillable = [
        'laporan_id', 'nama_file', 'path', 'keterangan',
        'is_utama', 'urutan', 'ukuran_bytes', 'mime_type',
    ];

    protected $casts = [
        'is_utama' => 'boolean',
    ];

    public function laporan()
    {
        return $this->belongsTo(OpkLaporan::class, 'laporan_id');
    }

    public function getUrlAttribute(): string
    {
        return asset('storage/' . $this->path);
    }
}
