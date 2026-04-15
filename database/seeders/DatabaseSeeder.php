<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@perusahaan.com'],
            [
                'name' => 'Administrator',
                'password' => 'password',
                'role' => 'admin',
            ]
        );
        User::updateOrCreate(
            ['email' => 'gudang@perusahaan.com'],
            [
                'name' => 'Staff Gudang',
                'password' => 'password',
                'role' => 'warehouse',
            ]
        );

        User::updateOrCreate(
            ['email' => 'driver@perusahaan.com'],
            [
                'name' => 'Driver Utama',
                'password' => 'password',
                'role' => 'driver',
            ]
        );
    }
}