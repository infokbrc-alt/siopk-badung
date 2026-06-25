<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $users = [
            [
                'name'      => 'Super Admin SIOPK',
                'email'     => env('SEEDER_SUPERADMIN_EMAIL', 'superadmin@siopk-badung.id'),
                'password'  => Hash::make(env('SEEDER_SUPERADMIN_PASSWORD', 'SiOPK@2025!')),
                'role'      => 'superadmin',
                'instansi'  => 'Dinas Kebudayaan Kabupaten Badung',
                'is_active' => true,
            ],
            [
                'name'      => 'Admin Dinas Kebudayaan',
                'email'     => env('SEEDER_ADMIN_EMAIL', 'admin@siopk-badung.id'),
                'password'  => Hash::make(env('SEEDER_ADMIN_PASSWORD', 'Admin@2025')),
                'role'      => 'admin',
                'instansi'  => 'Dinas Kebudayaan Kabupaten Badung',
                'is_active' => true,
            ],
            [
                'name'      => 'Verifikator OPK',
                'email'     => env('SEEDER_VERIFIKATOR_EMAIL', 'verifikator@siopk-badung.id'),
                'password'  => Hash::make(env('SEEDER_VERIFIKATOR_PASSWORD', 'Verif@2025')),
                'role'      => 'verifikator',
                'instansi'  => 'Dinas Kebudayaan Kabupaten Badung',
                'is_active' => true,
            ],
            [
                'name'      => 'Petugas Lapangan',
                'email'     => env('SEEDER_PETUGAS_EMAIL', 'petugas@siopk-badung.id'),
                'password'  => Hash::make(env('SEEDER_PETUGAS_PASSWORD', 'Petugas@2025')),
                'role'      => 'petugas',
                'instansi'  => 'Dinas Kebudayaan Kabupaten Badung',
                'is_active' => true,
            ],
        ];

        foreach ($users as $user) {
            User::updateOrCreate(
                ['email' => $user['email']],
                $user
            );
        }
    }
}
