<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'      => 'Super Admin SIOPK',
                'email'     => 'superadmin@siopk-badung.id',
                'password'  => Hash::make('SiOPK@2025!'),
                'role'      => 'superadmin',
                'instansi'  => 'Dinas Kebudayaan Kabupaten Badung',
                'is_active' => true,
            ],
            [
                'name'      => 'Admin Dinas Kebudayaan',
                'email'     => 'admin@siopk-badung.id',
                'password'  => Hash::make('Admin@2025'),
                'role'      => 'admin',
                'instansi'  => 'Dinas Kebudayaan Kabupaten Badung',
                'is_active' => true,
            ],
            [
                'name'      => 'Verifikator OPK',
                'email'     => 'verifikator@siopk-badung.id',
                'password'  => Hash::make('Verif@2025'),
                'role'      => 'verifikator',
                'instansi'  => 'Dinas Kebudayaan Kabupaten Badung',
                'is_active' => true,
            ],
            [
                'name'      => 'Petugas Lapangan',
                'email'     => 'petugas@siopk-badung.id',
                'password'  => Hash::make('Petugas@2025'),
                'role'      => 'petugas',
                'instansi'  => 'Dinas Kebudayaan Kabupaten Badung',
                'is_active' => true,
            ],
        ];

        foreach ($users as $user) {
            DB::table('users')->insert(array_merge($user, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
    }
}
