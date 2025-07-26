<?php

namespace App\Filament\Resources\KategoriPemasukanResource\Pages;

use App\Filament\Resources\KategoriPemasukanResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListKategoriPemasukans extends ListRecords
{
    protected static string $resource = KategoriPemasukanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
