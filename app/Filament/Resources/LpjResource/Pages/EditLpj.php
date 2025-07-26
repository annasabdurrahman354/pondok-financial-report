<?php

namespace App\Filament\Resources\LpjResource\Pages;

use App\Filament\Resources\LpjResource;
use App\Models\Lpj;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditLpj extends EditRecord
{
    protected static string $resource = LpjResource::class;
    protected string $recordStatus = 'diajukan';

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('back')
                ->label('Kembali')
                ->url($this->getResource()::getUrl('index'))
                ->color('gray'),
        ];
    }

    protected function getFormActions(): array
    {
        return [
            $this->getSaveFormAction()
                ->label(fn (Lpj $record) => $record->isDrafted() || $record->needsRevision() ? 'Ajukan' : 'Simpan'),

            Action::make('saveAsDraft')
                ->label('Simpan sebagai Draft')
                ->color('gray')
                ->action(function (Lpj $record) {
                    if ($record->status == 'revisi') {
                        $this->recordStatus = 'revisi';

                    }
                    else{
                        $this->recordStatus = 'draft';
                    }
                    $this->save();
                })
                ->visible(fn (Lpj $record) => $record->isDrafted() || $record->needsRevision()),

            $this->getCancelFormAction(),
        ];
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if (auth()->user()->isAdminPondok()) {
            $data['status'] = $this->recordStatus;
        }
        else {
            $this->recordStatus = $data['status'];
        }

        if ($this->recordStatus != 'revisi') {
            $data['pesan_revisi'] = null;
        }

        return $data;
    }

    protected function getSavedNotification(): ?Notification
    {
        if ($this->recordStatus === 'draft') {
            return Notification::make()
                ->success()
                ->title('LPJ berhasil disimpan sebagai draft')
                ->body('LPJ telah disimpan dan dapat diedit kembali nanti.');
        }
        else if ($this->recordStatus === 'revisi') {
            return Notification::make()
                ->success()
                ->title('LPJ berhasil disimpan')
                ->body('Revisi LPJ telah disimpan dan dapat diedit kembali nanti.');
        }
        else if ($this->recordStatus === 'diajukan') {
            return Notification::make()
                ->success()
                ->title('LPJ berhasil direvisi')
                ->body('LPJ telah berhasil direvisi dan diajukan kembali.');
        }
        else {
            return Notification::make()
                ->success()
                ->title('LPJ berhasil diubah')
                ->body('LPJ telah diubah langsung oleh admin pusat.');
        }
    }
}
