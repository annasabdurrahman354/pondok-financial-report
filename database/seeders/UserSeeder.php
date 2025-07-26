<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Pondok;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create sample pondok first
        $pondok1 = Pondok::create([
            'nama' => 'Pondok Pesantren Al-Hikmah',
            'nomor_telepon' => '081234567890',
            'alamat_lengkap' => 'Jl. Raya Pesantren No. 123',
            'provinsi' => 'Jawa Barat',
            'kota' => 'Bandung',
            'kecamatan' => 'Cibiru',
            'kelurahan' => 'Cibiru Hilir',
            'kode_pos' => '40615',
            'daerah_sambung' => 'Bandung Timur',
        ]);

        $pondok2 = Pondok::create([
            'nama' => 'Pondok Pesantren Darul Ulum',
            'nomor_telepon' => '081234567891',
            'alamat_lengkap' => 'Jl. Pesantren Darul Ulum No. 456',
            'provinsi' => 'Jawa Timur',
            'kota' => 'Malang',
            'kecamatan' => 'Lowokwaru',
            'kelurahan' => 'Dinoyo',
            'kode_pos' => '65144',
            'daerah_sambung' => 'Malang Kota',
        ]);

        // Create admin pusat
        User::create([
            'nama' => 'Super Admin',
            'email' => 'superadmin@yayasan.com',
            'nomor_telepon' => '085786537295',
            'password' => Hash::make('password'),
            'pondok_id' => null,
        ]);

        Artisan::call('shield:super-admin', ['--user' => 1]);

        // Create admin pusat
        User::create([
            'nama' => 'Admin Pusat',
            'email' => 'admin.pusat@yayasan.com',
            'nomor_telepon' => '081234567892',
            'password' => Hash::make('password'),
            'pondok_id' => null,
        ]);

        // Create admin pondok for each pondok
        User::create([
            'nama' => 'Admin Al-Hikmah',
            'email' => 'admin.alhikmah@yayasan.com',
            'nomor_telepon' => '081234567893',
            'password' => Hash::make('password'),
            'pondok_id' => $pondok1->id,
        ]);

        User::create([
            'nama' => 'Admin Darul Ulum',
            'email' => 'admin.darululum@yayasan.com',
            'nomor_telepon' => '081234567894',
            'password' => Hash::make('password'),
            'pondok_id' => $pondok2->id,
        ]);
    }
}

