<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $petugas = User::firstOrCreate(
            ['email' => 'petugas@ppks.test'],
            ['name' => 'Petugas Piket', 'password' => bcrypt('password')]
        );
        $petugas->assignRole('petugas-piket');

        $peksos = User::firstOrCreate(
            ['email' => 'peksos@ppks.test'],
            ['name' => 'Peksos', 'password' => bcrypt('password')]
        );
        $peksos->assignRole('peksos');

        $adminRps = User::firstOrCreate(
            ['email' => 'admin@ppks.test'],
            ['name' => 'Admin RPS', 'password' => bcrypt('password')]
        );
        $adminRps->assignRole('admin-rps');

        $kepala = User::firstOrCreate(
            ['email' => 'kepala@ppks.test'],
            ['name' => 'Kepala RPS', 'password' => bcrypt('password')]
        );
        $kepala->assignRole('kepala-rps');
    }
}