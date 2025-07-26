<?php

namespace App\Filament\Resources\PondokResource\Pages;

use App\Filament\Resources\PondokResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPondok extends ViewRecord
{
    protected static string $resource = PondokResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
