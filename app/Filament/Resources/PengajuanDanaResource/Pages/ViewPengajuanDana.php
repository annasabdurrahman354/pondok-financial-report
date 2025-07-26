<?php

namespace App\Filament\Resources\PengajuanDanaResource\Pages;

use App\Filament\Resources\PengajuanDanaResource;
use App\Models\PengajuanDana;
use Filament\Actions;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Pages\ViewRecord;
use Filament\Infolists\Components\Actions\Action;

class ViewPengajuanDana extends ViewRecord
{
    protected static string $resource = PengajuanDanaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('back')
                ->label('Kembali')
                ->url($this->getResource()::getUrl('index'))
                ->color('gray'),

            Actions\EditAction::make()
                ->label(fn (PengajuanDana $record) => $record->isDrafted() ? 'Ubah' : 'Revisi')
                ->color('warning')
                ->icon('heroicon-o-pencil')
                ->visible(fn (PengajuanDana $record) =>
                auth()->user()->isAdminPusat()
                    ? !$record->isAccepted()
                    : ($record->isDrafted() || $record->needsRevision())
                ),

            Actions\Action::make('submit')
                ->label('Submit')
                ->color('success')
                ->icon('heroicon-o-paper-airplane')
                ->visible(fn (PengajuanDana $record) => $record->isDrafted())
                ->action(fn (PengajuanDana $record) => $record->submit()),

            Actions\Action::make('requestRevision')
                ->label('Minta Revisi')
                ->color('danger')
                ->icon('heroicon-o-arrow-up-on-square-stack')
                ->form([
                    Textarea::make('pesan_revisi')
                        ->label('Pesan Revisi')
                        ->required()
                ])
                ->visible(fn (PengajuanDana $record) => auth()->user()->isAdminPusat() && $record->isSubmitted())
                ->action(function (array $data, PengajuanDana $record): void {
                    $record->requestRevision($data['pesan_revisi']);
                }),

            Actions\Action::make('accept')
                ->label('Terima')
                ->color('success')
                ->icon('heroicon-o-check')
                ->visible(fn (PengajuanDana $record) => auth()->user()->isAdminPusat() && $record->isSubmitted())
                ->action(fn (PengajuanDana $record) => $record->accept()),

            Actions\DeleteAction::make()
                ->hiddenLabel()
                ->color('danger')
                ->icon('heroicon-o-trash')
                ->visible(fn (PengajuanDana $record) => auth()->user()->isAdminPusat() && !$record->isAccepted()),
        ];
    }
}
