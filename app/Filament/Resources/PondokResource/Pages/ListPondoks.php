<?php

namespace App\Filament\Resources\PondokResource\Pages;

use App\Filament\Resources\PondokResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListPondoks extends ListRecords
{
    protected static string $resource = PondokResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
