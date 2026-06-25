<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class WilayahSeeder extends Seeder
{
    public function run(): void
    {
        $wilayah = [
            [
                'nama'  => 'Mengwi',
                'kode'  => '5103010',
                'desa_dinas' => [
                    'Baha','Buduk','Gulingan','Kapal','Kekeran','Kuwum',
                    'Lukluk','Mengwi','Munggu','Penarungan','Pererenan',
                    'Sading','Sembung','Sempidi','Werdi Bhuwana',
                ],
                'desa_adat' => [
                    'Desa Adat Mengwi','Desa Adat Kapal','Desa Adat Sempidi',
                    'Desa Adat Sading','Desa Adat Lukluk','Desa Adat Buduk',
                    'Desa Adat Munggu','Desa Adat Pererenan',
                ],
            ],
            [
                'nama'  => 'Abiansemal',
                'kode'  => '5103020',
                'desa_dinas' => [
                    'Abiansemal','Ayunan','Bongkasa','Bongkasa Pertiwi',
                    'Blahkiuh','Darmasaba','Getasan','Jagapati',
                    'Mekar Bhuana','Punggul','Sibang Gede','Sibang Kaja','Sedang',
                ],
                'desa_adat' => [
                    'Desa Adat Abiansemal','Desa Adat Blahkiuh',
                    'Desa Adat Bongkasa','Desa Adat Sibang Gede',
                    'Desa Adat Sedang',
                ],
            ],
            [
                'nama'  => 'Petang',
                'kode'  => '5103030',
                'desa_dinas' => [
                    'Belok','Carangsari','Getasan','Pangsan',
                    'Pelaga','Petang','Sulangai',
                ],
                'desa_adat' => [
                    'Desa Adat Petang','Desa Adat Sulangai',
                    'Desa Adat Pelaga','Desa Adat Carangsari',
                ],
            ],
            [
                'nama'  => 'Kuta',
                'kode'  => '5103040',
                'desa_dinas' => [
                    'Kedonganan','Kuta','Legian','Seminyak','Tuban',
                ],
                'desa_adat' => [
                    'Desa Adat Kuta','Desa Adat Legian',
                    'Desa Adat Seminyak','Desa Adat Tuban','Desa Adat Kedonganan',
                ],
            ],
            [
                'nama'  => 'Kuta Utara',
                'kode'  => '5103050',
                'desa_dinas' => [
                    'Canggu','Dalung','Kerobokan','Kerobokan Kaja',
                    'Kerobokan Kelod','Tibubeneng',
                ],
                'desa_adat' => [
                    'Desa Adat Kerobokan','Desa Adat Dalung',
                    'Desa Adat Canggu','Desa Adat Tibubeneng',
                ],
            ],
            [
                'nama'  => 'Kuta Selatan',
                'kode'  => '5103060',
                'desa_dinas' => [
                    'Benoa','Jimbaran','Kutuh','Pecatu',
                    'Tanjung Benoa','Ungasan',
                ],
                'desa_adat' => [
                    'Desa Adat Jimbaran','Desa Adat Pecatu',
                    'Desa Adat Ungasan','Desa Adat Kutuh',
                    'Desa Adat Tanjung Benoa',
                ],
            ],
        ];

        foreach ($wilayah as $w) {
            $kecId = DB::table('kecamatans')->insertGetId([
                'nama'       => $w['nama'],
                'kode'       => $w['kode'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            foreach ($w['desa_dinas'] as $desa) {
                DB::table('desa_dinas')->insert([
                    'kecamatan_id' => $kecId,
                    'nama'         => $desa,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }

            foreach ($w['desa_adat'] as $adat) {
                DB::table('desa_adats')->insert([
                    'kecamatan_id' => $kecId,
                    'nama'         => $adat,
                    'created_at'   => now(),
                    'updated_at'   => now(),
                ]);
            }
        }
    }
}
