<?php

namespace App\Filament\Resources\LpjResource\Pages;

use App\Filament\Resources\LpjResource;
use App\Models\Lpj;
use Filament\Actions;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ViewRecord;

class ViewLpj extends ViewRecord
{
    protected static string $resource = LpjResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Kembali')
                ->url($this->getResource()::getUrl('index'))
                ->color('gray'),

            Actions\EditAction::make('edit')
                ->label(fn (Lpj $record) => $record->isDrafted() ? 'Ubah' : 'Revisi')
                ->color('warning')
                ->icon('heroicon-o-pencil')
                ->visible(fn (Lpj $record) =>
                    auth()->user()->isAdminPusat()
                        ? !$record->isAccepted()
                        : ($record->isDrafted() || $record->needsRevision())
                ),

            Actions\Action::make('submit')
                ->label('Submit')
                ->color('success')
                ->icon('heroicon-o-paper-airplane')
                ->visible(fn (Lpj $record) => $record->isDrafted())
                ->action(fn (Lpj $record) => $record->submit()),

            Actions\Action::make('requestRevision')
                ->label('Minta Revisi')
                ->color('danger')
                ->icon('heroicon-o-arrow-up-on-square-stack')
                ->form([
                    Textarea::make('pesan_revisi')
                        ->label('Pesan Revisi')
                        ->required()
                ])
                ->visible(fn (Lpj $record) => auth()->user()->isAdminPusat() && $record->isSubmitted())
                ->action(function (array $data, Lpj $record): void {
                    $record->requestRevision($data['pesan_revisi']);
                }),

            Actions\Action::make('accept')
                ->label('Terima')
                ->color('success')
                ->icon('heroicon-o-check')
                ->visible(fn (Lpj $record) => auth()->user()->isAdminPusat() && $record->isSubmitted())
                ->action(fn (Lpj $record) => $record->accept()),

            Actions\DeleteAction::make()
                ->hiddenLabel()
                ->color('danger')
                ->icon('heroicon-o-trash')
                ->visible(fn (Lpj $record) => auth()->user()->isAdminPusat() && !$record->isAccepted()),
        ];
    }
}
