<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Filament\Facades\Filament;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $this->call([
            RolesAndPermissionsSeeder::class,
            UserSeeder::class,
            KategoriPemasukanSeeder::class,
            KategoriPengeluaranSeeder::class
        ]);

        $panels = Filament::getPanels();
        foreach ($panels as $panelId => $panel) {
            Filament::setCurrentPanel($panel);
            Artisan::call('shield:generate', [
                '--all' => true,
                '--panel' => $panelId,
            ]);
        }
    }
}
