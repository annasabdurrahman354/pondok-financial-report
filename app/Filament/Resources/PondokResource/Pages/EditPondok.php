<?php

namespace App\Filament\Resources\PondokResource\Pages;

use App\Filament\Resources\PondokResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPondok extends EditRecord
{
    protected static string $resource = PondokResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }
}
