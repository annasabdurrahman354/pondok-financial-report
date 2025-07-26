<?php

namespace App\Filament\Resources\RabResource\Pages;

use App\Filament\Resources\RabResource;
use App\Models\Periode;
use App\Models\Lpj;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateRab extends CreateRecord
{
    protected static string $resource = RabResource::class;
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
                ->title('LPJ berhasil disimpan sebagai draft')
                ->body('LPJ telah disimpan dan dapat diedit kembali nanti.');
        }

        else if ($this->recordStatus === 'diajukan'){
            return Notification::make()
                ->success()
                ->title('LPJ berhasil dibuat')
                ->body('LPJ telah diajukan untuk persetujuan admin pusat.');
        }
        else {
            return Notification::make()
                ->success()
                ->title('LPJ berhasil dibuat')
                ->body('LPJ telah dibuat langsung oleh admin pusat.');
        }
    }

    protected function fillForm(): void
    {
        $data = [];

        // Set default periode
        $activePeriod = Periode::getPeriodeRabAktif();
        if ($activePeriod) {
            $data['periode_id'] = $activePeriod->id;
        }

        // Prepare default pemasukan with "Sisa Saldo"
        $previousPeriod = Periode::getPeriodeRabBulanSebelumAktif();
        $sisaSaldoNominal = 0;

        if ($previousPeriod) {
            $previousLpj = Lpj::where('pondok_id', auth()->user()->pondok_id)
                ->where('periode_id', $previousPeriod->id)
                ->first();

            if ($previousLpj) {
                $sisaSaldoNominal = $previousLpj->saldo_realisasi;
            }
        }

        $data['pemasukan'] = [
            [
                'kategori_pemasukan_id' => 1,
                'nama' => 'Sisa Saldo',
                'detail' => 'Sisa saldo dari periode sebelumnya',
                'nominal' => $sisaSaldoNominal,
            ]
        ];

        // Default pengeluaran
        $data['pengeluaran'] = [
            [
                'kategori_pengeluaran_id' => null,
                'nama' => '',
                'detail' => '',
                'nominal' => 0,
            ]
        ];

        $this->form->fill($data);
    }
}
