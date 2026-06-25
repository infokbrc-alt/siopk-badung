<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OpkCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['nomor' => 1,  'nama' => 'Tradisi Lisan',            'ikon' => '🗣️',  'deskripsi' => 'Tutur, pantun, peribahasa, folklor, cerita rakyat'],
            ['nomor' => 2,  'nama' => 'Manuskrip',                'ikon' => '📜',  'deskripsi' => 'Lontar, babad, prasi, naskah kuno'],
            ['nomor' => 3,  'nama' => 'Adat Istiadat',            'ikon' => '🏛️',  'deskripsi' => 'Hukum adat, sistem nilai, tata kelola komunitas'],
            ['nomor' => 4,  'nama' => 'Ritus',                    'ikon' => '🙏',  'deskripsi' => 'Upacara keagamaan, siklus kehidupan, ritual pertanian'],
            ['nomor' => 5,  'nama' => 'Pengetahuan Tradisional',  'ikon' => '🌿',  'deskripsi' => 'Pengobatan, etnobotani, astronomi, ekologi lokal'],
            ['nomor' => 6,  'nama' => 'Teknologi Tradisional',    'ikon' => '⚙️',  'deskripsi' => 'Subak, tenun, gamelan, arsitektur tradisional'],
            ['nomor' => 7,  'nama' => 'Seni',                     'ikon' => '🎭',  'deskripsi' => 'Tari, musik, rupa, ukir, lukis, pertunjukan'],
            ['nomor' => 8,  'nama' => 'Bahasa',                   'ikon' => '💬',  'deskripsi' => 'Bahasa daerah, dialek, aksara lokal'],
            ['nomor' => 9,  'nama' => 'Permainan Rakyat',         'ikon' => '🎯',  'deskripsi' => 'Magoak-goakan, mekotek, ngaben sapi, dll'],
            ['nomor' => 10, 'nama' => 'Olahraga Tradisional',     'ikon' => '🥋',  'deskripsi' => 'Mepantigan, mekepung, peresean'],
        ];

        foreach ($categories as $cat) {
            DB::table('opk_categories')->insert([
                'nomor'      => $cat['nomor'],
                'nama'       => $cat['nama'],
                'ikon'       => $cat['ikon'],
                'deskripsi'  => $cat['deskripsi'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
