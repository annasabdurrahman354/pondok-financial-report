<?php

namespace App\Filament\Resources\PengajuanDanaResource\Pages;

use App\Filament\Resources\PengajuanDanaResource;
use App\Models\Periode;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreatePengajuanDana extends CreateRecord
{
    protected static string $resource = PengajuanDanaResource::class;
    protected static bool $canCreateAnother = false;
    protected string $recordStatus = 'diajukan';

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
            $this->getCreateFormAction()
                ->label(fn () => auth()->user()->isAdminPondok() ? 'Ajukan' : 'Simpan'),

            Action::make('createDraft')
                ->label('Simpan sebagai Draft')
                ->color('gray')
                ->action(function () {
                    $this->recordStatus = 'draft';
                    $this->create();
                }),

            $this->getCancelFormAction(),
        ];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (auth()->user()->isAdminPondok()){
            $data['pondok_id'] = auth()->user()->pondok_id;
            $activePeriod = Periode::getPeriodeRabAktif();
            if ($activePeriod) {
                $data['periode_id'] = $activePeriod->id;
            }
        }

        if (empty($data['status'])) {
            $data['status'] = $this->recordStatus;
        }
        else {
            $this->recordStatus = $data['status'];
        }

        return $data;
    }

    protected function getCreatedNotification(): ?Notification
    {
        if ($this->recordStatus === 'draft') {
            return Notification::make()
                ->success()
                ->title('Pengajuan dana berhasil disimpan sebagai draft')
                ->body('Pengajuan dana telah disimpan dan dapat diedit kembali nanti.');
        }
        else if ($this->recordStatus === 'diajukan'){
            return Notification::make()
                ->success()
                ->title('Pengajuan dana berhasil dibuat')
                ->body('Pengajuan dana telah diajukan untuk persetujuan admin pusat.');
        }
        else {
            return Notification::make()
                ->success()
                ->title('Pengajuan dana berhasil dibuat')
                ->body('Pengajuan dana telah dibuat langsung oleh admin pusat.');
        }
    }

    protected function fillForm(): void
    {
        $activePeriod = Periode::getPeriodeRabAktif();

        if ($activePeriod) {
            $this->form->fill([
                'periode_id' => $activePeriod->id,
            ]);
        }
    }
}
