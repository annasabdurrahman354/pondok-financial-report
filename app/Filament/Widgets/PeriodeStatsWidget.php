<?php

namespace App\Filament\Widgets;

use App\Models\Periode;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Carbon\Carbon;

class PeriodeStatsWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        // Get current period info
        $currentPeriodId = Carbon::now()->format('Ym'); // Format: YYYYMM (e.g., 202501)
        $currentPeriod = Periode::find($currentPeriodId);
        $latestPeriod = Periode::latest('id')->first();

        // Check if current month period exists
        $currentMonthExists = $currentPeriod !== null;

        // Get timeline info for latest period
        $timelineInfo = '';
        $timelineColor = 'gray';
        if ($latestPeriod) {
            $mulai = Carbon::parse($latestPeriod->mulai);
            $selesai = Carbon::parse($latestPeriod->selesai);
            $now = Carbon::now();

            if ($now->lt($mulai)) {
                $timelineInfo = 'Belum dimulai (' . $mulai->diffForHumans() . ')';
                $timelineColor = 'warning';
            } elseif ($now->between($mulai, $selesai)) {
                $timelineInfo = 'Sedang berjalan (berakhir ' . $selesai->diffForHumans() . ')';
                $timelineColor = 'success';
            } else {
                $timelineInfo = 'Sudah berakhir (' . $selesai->diffForHumans() . ')';
                $timelineColor = 'danger';
            }
        }

        return [
            Stat::make('Periode Aktif Terakhir', $latestPeriod ? $latestPeriod->keterangan : 'Tidak ada')
                ->description($timelineInfo)
                ->descriptionIcon('heroicon-m-clock')
                ->color($timelineColor),

            Stat::make('Periode Bulan Ini', $currentMonthExists ? 'Sudah dibuat' : 'Belum dibuat')
                ->description('Periode: ' . Carbon::now()->format('F Y'))
                ->descriptionIcon($currentMonthExists ? 'heroicon-m-check-circle' : 'heroicon-m-x-circle')
                ->color($currentMonthExists ? 'success' : 'danger'),
        ];
    }

    protected function getColumns(): int
    {
        return 2; // 2 columns in one row
    }

    public static function canView(): bool
    {
        return auth()->user()->isAdminPondok();
    }
}
