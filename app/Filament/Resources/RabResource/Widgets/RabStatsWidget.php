<?php

namespace App\Filament\Resources\RabResource\Widgets;

use App\Filament\Resources\RabResource;
use App\Models\Rab;
use App\Models\Pondok;
use App\Models\Periode;
use App\Enums\LaporanStatus;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseStatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class RabStatsWidget extends BaseStatsOverviewWidget
{
    protected function getStats(): array
    {
        $user = Auth::user();

        if ($user->isSuperAdmin() || $user->isAdminPusat()) {
            return $this->getSuperAdminStats();
        } elseif ($user->isAdminPondok()) {
            return $this->getAdminPondokStats();
        }

        return [];
    }

    private function getSuperAdminStats(): array
    {
        $periode = Periode::getPeriodeRabAktif();
        $isActivePeriod = $periode !== null;

        if (!$periode) {
            $periode = Periode::orderBy('id', 'desc')->first();
        }

        if (!$periode) {
            return [
                Stat::make('Status', 'Tidak ada periode RAB')
                    ->description('Silakan buat periode RAB terlebih dahulu')
                    ->color('warning')
            ];
        }

        $rabs = Rab::where('periode_id', $periode->id)->get();
        $totalPemasukan = $rabs->sum('total_pemasukan');
        $totalPengeluaran = $rabs->sum('total_pengeluaran');
        $saldo = $totalPemasukan - $totalPengeluaran;

        $allPondok = Pondok::get()->count();
        $pondokFilled = $rabs->pluck('pondok_id')->unique()->count();
        $pondokFilledAndAccepted = $rabs->where('status', LaporanStatus::DITERIMA)->pluck('pondok_id')->unique()->count();
        $pondokNotFilled = $allPondok - $pondokFilled;

        $stats = [
            Stat::make('Total Pemasukan', 'Rp ' . number_format($totalPemasukan, 0, ',', '.'))
                ->description("dari RAB {$periode->formatted_periode}")
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),

            Stat::make('Total Pengeluaran', 'Rp ' . number_format($totalPengeluaran, 0, ',', '.'))
                ->description("dari RAB {$periode->formatted_periode}")
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('danger'),

            Stat::make('Saldo', 'Rp ' . number_format($saldo, 0, ',', '.'))
                ->description($saldo >= 0 ? 'Surplus' : 'Defisit')
                ->descriptionIcon($saldo >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($saldo >= 0 ? 'success' : 'danger'),

            Stat::make('Pondok Sudah Isi', $pondokFilled . ' / ' . $allPondok)
                ->description(round(($pondokFilledAndAccepted / max($pondokFilled, 1)) * 100, 1) . '% telah diterima')
                ->descriptionIcon(round(($pondokFilledAndAccepted / max($pondokFilled, 1)) * 100, 1) != 100 ? 'heroicon-m-exclamation-triangle' : 'heroicon-m-check-circle')
                ->color(round(($pondokFilledAndAccepted / max($pondokFilled, 1)) * 100, 1) != 100 ? 'danger' : 'success')
        ];

        if (!$isActivePeriod) {
            $stats[] = Stat::make('Status Periode', 'Tidak Aktif')
                ->description('Periode: ' . Carbon::parse($periode->batas_awal_rab)->format('d M') . ' - ' . Carbon::parse($periode->batas_akhir_rab)->format('d M Y'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning');
        } else {
            $daysLeft = Carbon::parse($periode->batas_akhir_rab)->diffForHumans();
            $stats[] = Stat::make('Periode Aktif', 'Berakhir ' . $daysLeft)
                ->description('Berakhir: ' . Carbon::parse($periode->batas_akhir_rab)->format('d M Y'))
                ->descriptionIcon('heroicon-m-clock')
                ->color($daysLeft <= 3 ? 'danger' : ($daysLeft <= 7 ? 'warning' : 'success'));
        }

        if ($pondokNotFilled > 0) {
            $stats[] = Stat::make('Belum Mengisi', $pondokNotFilled . ' pondok')
                ->description('Memerlukan tindak lanjut')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('warning');
        }

        return $stats;
    }

    private function getAdminPondokStats(): array
    {
        $user = Auth::user();
        $periode = Periode::getPeriodeRabAktif();
        $isInPeriod = $periode !== null;

        if (!$periode) {
            $periode = Periode::orderBy('id', 'desc')->first();
        }

        if (!$periode) {
            return [
                Stat::make('Status', 'Tidak ada periode RAB')
                    ->description('Menunggu periode RAB dibuka')
                    ->color('warning')
            ];
        }

        $rab = Rab::where('pondok_id', $user->pondok_id)
            ->where('periode_id', $periode->id)
            ->first();

        $stats = [];

        if (!$isInPeriod) {
            $stats[] = Stat::make('Periode RAB', 'Belum Aktif')
                ->description('Mulai: ' . Carbon::parse($periode->batas_awal_rab)->format('d M Y'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('warning');

            $stats[] = Stat::make('Berakhir', Carbon::parse($periode->batas_akhir_rab)->format('d M Y'))
                ->description($periode->formatted_periode)
                ->descriptionIcon('heroicon-m-clock')
                ->color('info');
        } else {
            $daysLeft = Carbon::parse($periode->batas_akhir_rab)->diffForHumans();
            $stats[] = Stat::make('Periode Aktif', 'Berakhir ' . $daysLeft)
                ->description('Berakhir: ' . Carbon::parse($periode->batas_akhir_rab)->format('d M Y'))
                ->descriptionIcon('heroicon-m-clock')
                ->color($daysLeft <= 3 ? 'danger' : ($daysLeft <= 7 ? 'warning' : 'success'));
        }

        if ($rab) {
            $stats[] = Stat::make('Status RAB', $this->getStatusLabel($rab->status))
                ->description($rab->status === LaporanStatus::REVISI ? 'Perlu diperbaiki' : 'RAB tersedia')
                ->descriptionIcon($this->getStatusIcon($rab->status))
                ->color($this->getStatusColor($rab->status));

            if ($rab->status === LaporanStatus::REVISI && $rab->pesan_revisi) {
                $stats[] = Stat::make('Pesan Revisi', substr($rab->pesan_revisi, 0, 50) . '...')
                    ->description('Klik untuk detail lengkap')
                    ->descriptionIcon('heroicon-m-chat-bubble-left-ellipsis')
                    ->color('warning');
            }

            $stats[] = Stat::make('Total Pemasukan', 'Rp ' . number_format($rab->total_pemasukan, 0, ',', '.'))
                ->description('Rencana anggaran')
                ->descriptionIcon('heroicon-m-arrow-up')
                ->color('success');

            $stats[] = Stat::make('Total Pengeluaran', 'Rp ' . number_format($rab->total_pengeluaran, 0, ',', '.'))
                ->description('Rencana belanja')
                ->descriptionIcon('heroicon-m-arrow-down')
                ->color('danger');

            $saldo = $rab->saldo;
            $stats[] = Stat::make('Saldo', 'Rp ' . number_format($saldo, 0, ',', '.'))
                ->description($saldo >= 0 ? 'Surplus' : 'Defisit')
                ->descriptionIcon($saldo >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($saldo >= 0 ? 'success' : 'danger');
        } else {
            $stats[] = Stat::make('Status RAB', 'Belum Mengisi')
                ->description($isInPeriod ? 'Segera isi sebelum batas waktu' : 'Menunggu periode aktif')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color($isInPeriod ? 'danger' : 'warning');

            if ($isInPeriod) {
                $stats[] = Stat::make('Action Required', 'Isi RAB')
                    ->description('Data RAB diperlukan')
                    ->descriptionIcon('heroicon-m-document-plus')
                    ->color('danger')
                    ->url(RabResource::getUrl('create'));
            }
        }

        return $stats;
    }

    private function getStatusLabel($status): string
    {
        return match($status) {
            LaporanStatus::DRAFT => 'Draft',
            LaporanStatus::DIAJUKAN => 'Diajukan',
            LaporanStatus::DITERIMA => 'Diterima',
            LaporanStatus::REVISI => 'Revisi',
            default => 'Unknown'
        };
    }

    private function getStatusIcon($status): string
    {
        return match($status) {
            LaporanStatus::DRAFT => 'heroicon-m-document',
            LaporanStatus::DIAJUKAN => 'heroicon-m-paper-airplane',
            LaporanStatus::DITERIMA => 'heroicon-m-check-circle',
            LaporanStatus::REVISI => 'heroicon-m-arrow-path',
            default => 'heroicon-m-question-mark-circle'
        };
    }

    private function getStatusColor($status): string
    {
        return match($status) {
            LaporanStatus::DRAFT => 'gray',
            LaporanStatus::DIAJUKAN => 'warning',
            LaporanStatus::DITERIMA => 'success',
            LaporanStatus::REVISI => 'danger',
            default => 'gray'
        };
    }

    protected function getColumns(): int
    {
        return 3; // This sets the number of columns to 4
    }
}
