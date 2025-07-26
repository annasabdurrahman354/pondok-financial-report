<?php

namespace App\Filament\Resources\RabResource\Pages;

use App\Filament\Resources\RabResource;
use App\Models\Rab;
use Filament\Actions;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ViewRecord;

class ViewRab extends ViewRecord
{
    protected static string $resource = RabResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Kembali')
                ->url($this->getResource()::getUrl('index'))
                ->color('gray'),

            Actions\EditAction::make()
                ->label(fn (Rab $record) => $record->isDrafted() ? 'Ubah' : 'Revisi')
                ->color('warning')
                ->icon('heroicon-o-pencil')
                ->visible(fn (Rab $record) =>
                    auth()->user()->isAdminPusat()
                        ? !$record->isAccepted()
                        : ($record->isDrafted() || $record->needsRevision())
                ),

            Actions\Action::make('submit')
                ->label('Submit')
                ->color('success')
                ->icon('heroicon-o-paper-airplane')
                ->visible(fn (Rab $record) => $record->isDrafted())
                ->action(fn (Rab $record) => $record->submit()),

            Actions\Action::make('requestRevision')
                ->label('Minta Revisi')
                ->color('danger')
                ->icon('heroicon-o-arrow-up-on-square-stack')
                ->form([
                    Textarea::make('pesan_revisi')
                        ->label('Pesan Revisi')
                        ->required()
                ])
                ->visible(fn (Rab $record) => auth()->user()->isAdminPusat() && $record->isSubmitted())
                ->action(function (array $data, Rab $record): void {
                    $record->requestRevision($data['pesan_revisi']);
                }),

            Actions\Action::make('accept')
                ->label('Terima')
                ->color('success')
                ->icon('heroicon-o-check')
                ->visible(fn (Rab $record) => auth()->user()->isAdminPusat() && $record->isSubmitted())
                ->action(fn (Rab $record) => $record->accept()),

            Actions\DeleteAction::make()
                ->hiddenLabel()
                ->color('danger')
                ->icon('heroicon-o-trash')
                ->visible(fn (Rab $record) => auth()->user()->isAdminPusat() && !$record->isAccepted()),
        ];
    }
}
