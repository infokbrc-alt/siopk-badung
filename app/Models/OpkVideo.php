<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpkVideo extends Model
{
    protected $table = 'opk_videos';

    protected $fillable = [
        'laporan_id', 'nama_file', 'path', 'link_eksternal', 'keterangan',
    ];

    public function laporan()
    {
        return $this->belongsTo(OpkLaporan::class, 'laporan_id');
    }

    public function isLocal(): bool
    {
        return ! empty($this->path);
    }

    public function isExternal(): bool
    {
        return ! empty($this->link_eksternal);
    }
}
