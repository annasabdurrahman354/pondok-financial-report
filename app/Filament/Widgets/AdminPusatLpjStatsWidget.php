<?php

namespace App\Filament\Widgets;

use App\Models\Lpj;
use App\Models\Pondok;
use App\Enums\LaporanStatus;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AdminPusatLpjStatsWidget extends BaseWidget
{
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        // LPJ Stats
        $lpjStats = Lpj::selectRaw('status, count(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->toArray();

        // Get total pondok that haven't submitted LPJ (no LPJ + draft status)
        $totalPondok = Pondok::count();
        $lpjSubmitted = Lpj::whereIn('status', [
            LaporanStatus::DIAJUKAN->value,
            LaporanStatus::DITERIMA->value,
            LaporanStatus::REVISI->value
        ])->distinct('pondok_id')->count('pondok_id');
        $lpjBelumMengajukan = $totalPondok - $lpjSubmitted;

        return [
            Stat::make('LPJ - Belum Mengajukan', max(0, $lpjBelumMengajukan))
                ->description('Belum membuat + Draft')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('gray'),

            Stat::make('LPJ - Diajukan', $lpjStats[LaporanStatus::DIAJUKAN->value] ?? 0)
                ->description('Menunggu persetujuan | Revisi: ' . ($lpjStats[LaporanStatus::REVISI->value] ?? 0))
                ->descriptionIcon('heroicon-m-clock')
                ->color('warning'),

            Stat::make('LPJ - Diterima', $lpjStats[LaporanStatus::DITERIMA->value] ?? 0)
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
