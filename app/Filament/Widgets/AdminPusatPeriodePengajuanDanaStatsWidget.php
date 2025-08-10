<?php

namespace App\Filament\Widgets;

use App\Models\Periode;
use App\Models\PengajuanDana;
use App\Enums\LaporanStatus;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class AdminPusatPeriodePengajuanDanaStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Get current month period or latest period if current doesn't exist
        $currentPeriodId = Carbon::now()->format('Ym'); // Format: YYYYMM (e.g., 202501)
        $periode = Periode::find($currentPeriodId);

        if (!$periode) {
            $periode = Periode::latest('id')->first();
        }

        $stats = [];

        if ($periode) {
            // RAB Stats
            $now = Carbon::now();
            $batasAwalRab = Carbon::parse($periode->batas_awal_rab);
            $batasAkhirRab = Carbon::parse($periode->batas_akhir_rab);

            $rabStatus = '';
            $rabDescription = '';
            $rabColor = 'gray';
            $rabIcon = 'heroicon-m-document-text';

            if ($now->lt($batasAwalRab)) {
                $rabStatus = 'Belum Saatnya';
                $rabDescription = 'Mulai ' . $batasAwalRab->format('d M Y') . ' (' . $batasAwalRab->diffForHumans() . ')';
                $rabColor = 'warning';
                $rabIcon = 'heroicon-m-clock';
            } elseif ($now->between($batasAwalRab, $batasAkhirRab)) {
                $rabStatus = 'Sedang Aktif';
                $rabDescription = 'Berakhir ' . $batasAkhirRab->format('d M Y') . ' (' . $batasAkhirRab->diffForHumans() . ')';
                $rabColor = 'success';
                $rabIcon = 'heroicon-m-check-circle';
            } else {
                $rabStatus = 'Sudah Berakhir';
                $rabDescription = 'Berakhir ' . $batasAkhirRab->format('d M Y') . ' (' . $batasAkhirRab->diffForHumans() . ')';
                $rabColor = 'danger';
                $rabIcon = 'heroicon-m-x-circle';
            }

            $stats[] = Stat::make('Periode RAB', $rabStatus)
                ->description($rabDescription)
                ->descriptionIcon($rabIcon)
                ->color($rabColor);

            // LPJ Stats
            $batasAwalLpj = Carbon::parse($periode->batas_awal_lpj);
            $batasAkhirLpj = Carbon::parse($periode->batas_akhir_lpj);

            $lpjStatus = '';
            $lpjDescription = '';
            $lpjColor = 'gray';
            $lpjIcon = 'heroicon-m-document-check';

            if ($now->lt($batasAwalLpj)) {
                $lpjStatus = 'Belum Saatnya';
                $lpjDescription = 'Mulai ' . $batasAwalLpj->format('d M Y') . ' (' . $batasAwalLpj->diffForHumans() . ')';
                $lpjColor = 'warning';
                $lpjIcon = 'heroicon-m-clock';
            } elseif ($now->between($batasAwalLpj, $batasAkhirLpj)) {
                $lpjStatus = 'Sedang Aktif';
                $lpjDescription = 'Berakhir ' . $batasAkhirLpj->format('d M Y') . ' (' . $batasAkhirLpj->diffForHumans() . ')';
                $lpjColor = 'success';
                $lpjIcon = 'heroicon-m-check-circle';
            } else {
                $lpjStatus = 'Sudah Berakhir';
                $lpjDescription = 'Berakhir ' . $batasAkhirLpj->format('d M Y') . ' (' . $batasAkhirLpj->diffForHumans() . ')';
                $lpjColor = 'danger';
                $lpjIcon = 'heroicon-m-x-circle';
            }

            $stats[] = Stat::make('Periode LPJ', $lpjStatus)
                ->description($lpjDescription)
                ->descriptionIcon($lpjIcon)
                ->color($lpjColor);

            // Pengajuan Dana Stats
            $pengajuanStats = PengajuanDana::selectRaw('status, count(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
                ->toArray();

            $stats[] = Stat::make('Pengajuan Dana - Diajukan', $pengajuanStats[LaporanStatus::DIAJUKAN->value] ?? 0)
                ->description('Menunggu persetujuan | Revisi: ' . ($pengajuanStats[LaporanStatus::REVISI->value] ?? 0))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning');

            $stats[] = Stat::make('Pengajuan Dana - Diterima', $pengajuanStats[LaporanStatus::DITERIMA->value] ?? 0)
                ->description('Sudah disetujui')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success');

        } else {
            // No period found
            $stats = [
                Stat::make('Periode RAB', 'Tidak Ada Data')
                    ->description('Belum ada periode yang tersedia')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('gray'),

                Stat::make('Periode LPJ', 'Tidak Ada Data')
                    ->description('Belum ada periode yang tersedia')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('gray'),

                Stat::make('Pengajuan Dana - Diajukan', 0)
                    ->description('Data periode tidak tersedia')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('gray'),

                Stat::make('Pengajuan Dana - Diterima', 0)
                    ->description('Data periode tidak tersedia')
                    ->descriptionIcon('heroicon-m-exclamation-triangle')
                    ->color('gray'),
            ];
        }

        return $stats;
    }

    protected function getColumns(): int
    {
        return 4; // 4 columns in one row
    }

    public static function canView(): bool
    {
        return auth()->user()->isAdminPusat();
    }
}
