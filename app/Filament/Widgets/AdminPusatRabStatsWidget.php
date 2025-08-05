<?php

namespace App\Filament\Widgets;

use App\Models\Rab;
use App\Models\Pondok;
use App\Enums\LaporanStatus;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminPusatRabStatsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected function getStats(): array
    {
        // RAB Stats
        $rabStats = Rab::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Get total pondok that haven't submitted RAB (no RAB + draft status)
        $totalPondok = Pondok::count();
        $rabSubmitted = Rab::whereIn('status', [
            LaporanStatus::DIAJUKAN->value,
            LaporanStatus::DITERIMA->value,
            LaporanStatus::REVISI->value
        ])->distinct('pondok_id')->count('pondok_id');
        $rabBelumMengajukan = $totalPondok - $rabSubmitted;

        return [
            Stat::make('RAB - Belum Mengajukan', max(0, $rabBelumMengajukan))
                ->description('Belum membuat + Draft')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('gray'),

            Stat::make('RAB - Diajukan', $rabStats[LaporanStatus::DIAJUKAN->value] ?? 0)
                ->description('Menunggu persetujuan | Revisi: ' . ($rabStats[LaporanStatus::REVISI->value] ?? 0))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('RAB - Diterima', $rabStats[LaporanStatus::DITERIMA->value] ?? 0)
                ->description('Sudah disetujui')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),
        ];
    }

    protected function getColumns(): int
    {
        return 3; // 3 columns in one row
    }

    public static function canView(): bool
    {
        return auth()->user()->isAdminPusat();
    }
}
