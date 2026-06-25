<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpkRiwayatStatus extends Model
{
    protected $table = 'opk_riwayat_status';

    protected $fillable = [
        'laporan_id', 'status_lama', 'status_baru', 'user_id', 'catatan',
    ];

    public function laporan()
    {
        return $this->belongsTo(OpkLaporan::class, 'laporan_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
