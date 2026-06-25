<?php

namespace App\Console\Commands;

use App\Jobs\AnalisisOpkJob;
use App\Models\OpkLaporan;
use Illuminate\Console\Command;

class AnalisisSemuaOpkCommand extends Command
{
    protected $signature   = 'siopk:analisis-semua {--force : Analisis ulang semua, termasuk yang sudah punya score}';
    protected $description = 'Jalankan AI analisis untuk semua laporan yang belum punya AI score';

    public function handle(): int
    {
        $query = OpkLaporan::whereIn('status_verifikasi', ['menunggu', 'review_dinas', 'disetujui']);

        if (!$this->option('force')) {
            $query->whereNull('ai_urgency_score');
        }

        $laporans = $query->get();

        if ($laporans->isEmpty()) {
            $this->info('Tidak ada laporan yang perlu dianalisis.');
            return self::SUCCESS;
        }

        $this->info("Ditemukan {$laporans->count()} laporan untuk dianalisis.");

        $bar = $this->output->createProgressBar($laporans->count());
        $bar->start();

        foreach ($laporans as $laporan) {
            AnalisisOpkJob::dispatch($laporan->id);
            $bar->advance();
            // Jeda kecil agar tidak rate-limit API
            if (config('queue.default') === 'sync') {
                sleep(1);
            }
        }

        $bar->finish();
        $this->info('');
        $this->info("✓ {$laporans->count()} job AI berhasil di-dispatch.");

        return self::SUCCESS;
    }
}
