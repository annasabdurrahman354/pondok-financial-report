<?php
namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Filament\Actions\Action;
use Filament\Actions\Concerns\InteractsWithActions;
use Filament\Actions\Contracts\HasActions;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Notifications\Notification;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\LaporanExport;
use App\Models\Pondok;

class SimpleExportWidget extends Widget implements HasForms, HasActions
{
    use InteractsWithForms;
    use InteractsWithActions;

    protected static string $view = 'filament.widgets.simple-export-widget';

    public static function canView(): bool
    {
        return auth()->user()->isAdminPusat();
    }

    protected static ?int $sort = -1;


    protected function getViewData(): array
    {
        return [
            'totalPondok' => Pondok::count(),
        ];
    }

    public function exportAction(): Action
    {
        return Action::make('export')
            ->label('Export Laporan')
            ->icon('heroicon-o-arrow-down-tray')
            ->color('primary')
            ->size('lg')
            ->form([
                Section::make('Pilihan Export')
                    ->description('Tentukan data apa yang ingin di-export')
                    ->schema([
                        Select::make('export_types')
                            ->label('Tipe Laporan')
                            ->options([
                                'RAB' => 'RAB (Rencana Anggaran Belanja)',
                                'LPJ' => 'LPJ (Laporan Pertanggungjawaban)',
                                'Pengajuan Dana' => 'Pengajuan Dana',
                            ])
                            ->multiple()
                            ->required()
                            ->default(['RAB', 'LPJ', 'Pengajuan Dana'])
                            ->helperText('Pilih jenis laporan yang ingin di-export'),
                    ]),

                Section::make('Filter Periode')
                    ->description('Tentukan rentang periode yang ingin di-export')
                    ->schema([
                        Select::make('mulai_bulan')
                            ->label('Dari Bulan')
                            ->options([
                                '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
                                '04' => 'April', '05' => 'Mei', '06' => 'Juni',
                                '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
                                '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
                            ])
                            ->required()
                            ->default('01')
                            ->columnSpan(1),

                        TextInput::make('mulai_tahun')
                            ->label('Tahun')
                            ->numeric()
                            ->minValue(2020)
                            ->maxValue(2030)
                            ->required()
                            ->default(date('Y'))
                            ->columnSpan(1),

                        Select::make('sampai_bulan')
                            ->label('Sampai Bulan')
                            ->options([
                                '01' => 'Januari', '02' => 'Februari', '03' => 'Maret',
                                '04' => 'April', '05' => 'Mei', '06' => 'Juni',
                                '07' => 'Juli', '08' => 'Agustus', '09' => 'September',
                                '10' => 'Oktober', '11' => 'November', '12' => 'Desember',
                            ])
                            ->required()
                            ->default('12')
                            ->columnSpan(1),

                        TextInput::make('sampai_tahun')
                            ->label('Tahun')
                            ->numeric()
                            ->minValue(2020)
                            ->maxValue(2030)
                            ->required()
                            ->default(date('Y'))
                            ->columnSpan(1),
                    ])
                    ->columns(2),

                Section::make('Filter Pondok')
                    ->description('Pilih pondok yang ingin di-export')
                    ->schema([
                        Select::make('pondok_ids')
                            ->label('Pondok')
                            ->options(function () {
                                $options = ['all' => '✓ Semua Pondok (' . Pondok::count() . ' pondok)'];
                                return $options + Pondok::pluck('nama', 'id')->toArray();
                            })
                            ->multiple()
                            ->required()
                            ->default(['all'])
                            ->helperText('Pilih "Semua Pondok" atau pilih pondok spesifik yang ingin di-export'),
                    ]),
            ])
            ->action(function (array $data) {
                try {
                    // Validate periode range
                    $periodeStart = $data['mulai_tahun'] . str_pad($data['mulai_bulan'], 2, '0', STR_PAD_LEFT);
                    $periodeEnd = $data['sampai_tahun'] . str_pad($data['sampai_bulan'], 2, '0', STR_PAD_LEFT);

                    if ($periodeStart > $periodeEnd) {
                        Notification::make()
                            ->title('Error Validasi')
                            ->body('Periode mulai tidak boleh lebih besar dari periode sampai')
                            ->danger()
                            ->send();
                        return;
                    }

                    // Generate descriptive filename
                    $exportTypesStr = implode('-', $data['export_types']);
                    $filename = 'laporan_' . strtolower(str_replace(' ', '_', $exportTypesStr)) . '_' .
                        $periodeStart . '_' . $periodeEnd . '_' . date('Y-m-d_H-i-s') . '.xlsx';

                    // Show success notification
                    Notification::make()
                        ->title('Export Berhasil')
                        ->body('File Excel berhasil di-generate dan akan didownload')
                        ->success()
                        ->send();

                    // Create and download Excel file
                    return Excel::download(
                        new LaporanExport(
                            $data['export_types'],
                            $periodeStart,
                            $periodeEnd,
                            $data['pondok_ids']
                        ),
                        $filename
                    );

                } catch (\Exception $e) {
                    Notification::make()
                        ->title('Export Gagal')
                        ->body('Terjadi kesalahan: ' . $e->getMessage())
                        ->danger()
                        ->send();
                }
            })
            ->modalHeading('Export Laporan Data')
            ->modalDescription('Export data laporan ke file Excel dengan berbagai pilihan filter')
            ->modalSubmitActionLabel('Download Excel')
            ->modalWidth('2xl');
    }
}
