<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\KategoriPemasukan;

class KategoriPemasukanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['id' => 1, 'nama' => 'Sisa Saldo'],
            ['nama' => 'Donasi'],
            ['nama' => 'Infaq'],
            ['nama' => 'Zakat'],
            ['nama' => 'Bantuan Pemerintah'],
            ['nama' => 'Usaha Pondok'],
            ['nama' => 'Lain-lain'],
        ];

        foreach ($categories as $category) {
            KategoriPemasukan::updateOrCreate(
                ['nama' => $category['nama']],
                $category
            );
        }
    }
}

