<?php

namespace App\Filament\Resources\RabResource\Pages;

use App\Filament\Resources\RabResource;
use App\Models\Rab;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditRab extends EditRecord
{
    protected static string $resource = RabResource::class;
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
                ->label(fn (Rab $record) => $record->isDrafted() ? 'Ajukan' : 'Ajukan Kembali'),

            Action::make('saveAsDraft')
                ->label('Simpan sebagai Draft')
                ->color('gray')
                ->action(function (Rab $record) {
                    if ($record->status == 'revisi') {
                        $this->recordStatus = 'revisi';

                    }
                    else{
                        $this->recordStatus = 'draft';
                    }
                    $this->save();
                })
                ->visible(fn (Rab $record) => $record->isDrafted() || $record->needsRevision()),

            $this->getCancelFormAction(),
        ];
    }


    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['status'] = $this->recordStatus;
        // Only clear pesan_revisi when submitting (not when saving as draft)
        if ($this->recordStatus === 'diajukan') {
            $data['pesan_revisi'] = null;
        }

        return $data;
    }

    protected function getSavedNotification(): ?Notification
    {
        if ($this->recordStatus === 'draft') {
            return Notification::make()
                ->success()
                ->title('RAB berhasil disimpan sebagai draft')
                ->body('RAB telah disimpan dan dapat diedit kembali nanti.');
        }

        if ($this->recordStatus === 'revisi') {
            return Notification::make()
                ->success()
                ->title('RAB berhasil disimpan')
                ->body('Revisi LPJ telah disimpan dan dapat diedit kembali nanti.');
        }

        return Notification::make()
            ->success()
            ->title('RAB berhasil direvisi')
            ->body('RAB telah berhasil direvisi dan diajukan kembali.');
    }
}
