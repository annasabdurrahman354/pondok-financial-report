<?php

namespace App\Filament\Resources\KategoriPemasukanResource\Pages;

use App\Filament\Resources\KategoriPemasukanResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditKategoriPemasukan extends EditRecord
{
    protected static string $resource = KategoriPemasukanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
