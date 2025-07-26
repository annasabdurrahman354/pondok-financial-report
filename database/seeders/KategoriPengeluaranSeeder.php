<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\KategoriPengeluaran;

class KategoriPengeluaranSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            ['nama' => 'Operasional'],
            ['nama' => 'Ukhro'],
            ['nama' => 'Konsumsi Santri'],
            ['nama' => 'Pemeliharaan Gedung'],
            ['nama' => 'Listrik dan Air'],
            ['nama' => 'Kegiatan Pendidikan'],
            ['nama' => 'Kegiatan Keagamaan'],
            ['nama' => 'Kesehatan'],
            ['nama' => 'Transportasi'],
            ['nama' => 'Alat Tulis Kantor'],
            ['nama' => 'Lain-lain'],
        ];

        foreach ($categories as $category) {
            KategoriPengeluaran::updateOrCreate(
                ['nama' => $category['nama']],
                $category
            );
        }
    }
}

