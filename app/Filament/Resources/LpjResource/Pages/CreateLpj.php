<?php

namespace App\Filament\Resources\LpjResource\Pages;

use App\Filament\Resources\LpjResource;
use App\Models\Periode;
use App\Models\Rab;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateLpj extends CreateRecord
{
    protected static string $resource = LpjResource::class;
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
            $activePeriod = Periode::getPeriodeLpjAktif();
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
        $activePeriod = Periode::getPeriodeLpjAktif();
        $periodeId = $activePeriod?->id;

        $rab = Rab::where('pondok_id', auth()->user()->pondok_id)
            ->where('periode_id', $periodeId)
            ->where('status', 'diterima')
            ->first();

        $pemasukanData = [];
        $pengeluaranData = [];

        if ($rab) {
            foreach ($rab->pemasukan as $rabPemasukan) {
                $pemasukanData[] = [
                    'rab_pemasukan_id' => $rabPemasukan->id,
                    'kategori_pemasukan_id' => $rabPemasukan->kategori_pemasukan_id,
                    'nama' => $rabPemasukan->nama,
                    'detail' => $rabPemasukan->detail,
                    'nominal_rencana' => $rabPemasukan->nominal,
                    'nominal_realisasi' => 0,
                    'keterangan_realisasi' => '',
                ];
            }

            foreach ($rab->pengeluaran as $rabPengeluaran) {
                $pengeluaranData[] = [
                    'rab_pengeluaran_id' => $rabPengeluaran->id,
                    'kategori_pengeluaran_id' => $rabPengeluaran->kategori_pengeluaran_id,
                    'nama' => $rabPengeluaran->nama,
                    'detail' => $rabPengeluaran->detail,
                    'nominal_rencana' => $rabPengeluaran->nominal,
                    'nominal_realisasi' => 0,
                    'keterangan_realisasi' => '',
                ];
            }
        }
        else {
            $pemasukanData[] = [
                'kategori_pemasukan_id' => 1,
                'nama' => 'Sisa Saldo',
                'detail' => 'Sisa saldo dari periode sebelumnya',
                'nominal_rencana' => 0,
                'nominal_realisasi' => 0,
                'keterangan_realisasi' => '',
            ];
        }

        $this->form->fill([
            'periode_id' => $periodeId,
            'pemasukan' => $pemasukanData,
            'pengeluaran' => $pengeluaranData,
        ]);
    }
}
