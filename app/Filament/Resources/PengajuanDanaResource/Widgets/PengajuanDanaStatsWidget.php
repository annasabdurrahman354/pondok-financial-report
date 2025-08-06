<?php

namespace App\Filament\Resources\PengajuanDanaResource\Widgets;

use App\Models\PengajuanDana;
use App\Models\Pondok;
use App\Models\Periode;
use App\Enums\LaporanStatus;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseStatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class PengajuanDanaStatsWidget extends BaseStatsOverviewWidget
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
        $periode = Periode::getPeriodeRabAktif(); // Using same period as RAB
        $isActivePeriod = $periode !== null;

        if (!$periode) {
            $periode = Periode::orderBy('id', 'desc')->first();
        }

        if (!$periode) {
            return [
                Stat::make('Status', 'Tidak ada periode aktif')
                    ->description('Silakan buat periode terlebih dahulu')
                    ->color('warning')
            ];
        }

        $pengajuanDanas = PengajuanDana::where('periode_id', $periode->id)->get();
        $totalNominal = $pengajuanDanas->sum('nominal');
        $totalNominalDiterima = $pengajuanDanas->where('status', LaporanStatus::DITERIMA)->sum('nominal');

        $pondokMengajukan = $pengajuanDanas->pluck('pondok_id')->unique()->count();
        $pondokDiterima = $pengajuanDanas->where('status', LaporanStatus::DITERIMA)->pluck('pondok_id')->unique()->count();

        // Status breakdown
        $draft = $pengajuanDanas->where('status', LaporanStatus::DRAFT)->count();
        $diajukan = $pengajuanDanas->where('status', LaporanStatus::DIAJUKAN)->count();
        $diterima = $pengajuanDanas->where('status', LaporanStatus::DITERIMA)->count();
        $revisi = $pengajuanDanas->where('status', LaporanStatus::REVISI)->count();

        $stats = [
            Stat::make('Total Pengajuan', 'Rp ' . number_format($totalNominal, 0, ',', '.'))
                ->description("dari periode {$periode->formatted_periode}")
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('info'),

            Stat::make('Dana Disetujui', 'Rp ' . number_format($totalNominalDiterima, 0, ',', '.'))
                ->description($totalNominal > 0 ? round(($totalNominalDiterima / $totalNominal) * 100, 1) . '% dari total pengajuan' : '0% dari total')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Pondok Mengajukan', $pondokMengajukan)
                ->description($pondokDiterima . ' pengajuan diterima')
                ->descriptionIcon('heroicon-m-building-office-2')
                ->color('info'),

            Stat::make('Perlu Review', $diajukan)
                ->description('Pengajuan menunggu persetujuan')
                ->descriptionIcon('heroicon-m-clock')
                ->color($diajukan > 0 ? 'warning' : 'success')
        ];

        if ($revisi > 0) {
            $stats[] = Stat::make('Perlu Revisi', $revisi)
                ->description('Pengajuan dikembalikan')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('danger');
        }

        if (!$isActivePeriod) {
            $stats[] = Stat::make('Status Periode', 'Tidak Aktif')
                ->description('Periode: ' . Carbon::parse($periode->batas_awal_rab)->format('d M') . ' - ' . Carbon::parse($periode->batas_akhir_rab)->format('d M Y'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning');
        } else {
            $daysLeft = Carbon::parse($periode->batas_akhir_rab)->diffInDays(Carbon::now());
            $stats[] = Stat::make('Periode Aktif', $daysLeft . ' hari tersisa')
                ->description('Berakhir: ' . Carbon::parse($periode->batas_akhir_rab)->format('d M Y'))
                ->descriptionIcon('heroicon-m-clock')
                ->color($daysLeft <= 3 ? 'danger' : ($daysLeft <= 7 ? 'warning' : 'success'));
        }

        return $stats;
    }

    private function getAdminPondokStats(): array
    {
        $user = Auth::user();
        $periode = Periode::getPeriodeRabAktif(); // Using same period as RAB
        $isInPeriod = $periode !== null;

        if (!$periode) {
            $periode = Periode::orderBy('id', 'desc')->first();
        }

        if (!$periode) {
            return [
                Stat::make('Status', 'Tidak ada periode aktif')
                    ->description('Menunggu periode dibuka')
                    ->color('warning')
            ];
        }

        $pengajuanDana = PengajuanDana::where('pondok_id', $user->pondok_id)
            ->where('periode_id', $periode->id)
            ->first();

        $stats = [];

        if (!$isInPeriod) {
            $stats[] = Stat::make('Periode', 'Belum Aktif')
                ->description('Mulai: ' . Carbon::parse($periode->batas_awal_rab)->format('d M Y'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('warning');

            $stats[] = Stat::make('Berakhir', Carbon::parse($periode->batas_akhir_rab)->format('d M Y'))
                ->description($periode->formatted_periode)
                ->descriptionIcon('heroicon-m-clock')
                ->color('info');
        } else {
            $daysLeft = Carbon::parse($periode->batas_akhir_rab)->diffInDays(Carbon::now());
            $stats[] = Stat::make('Periode Aktif', $daysLeft . ' hari tersisa')
                ->description('Berakhir: ' . Carbon::parse($periode->batas_akhir_rab)->format('d M Y'))
                ->descriptionIcon('heroicon-m-clock')
                ->color($daysLeft <= 3 ? 'danger' : ($daysLeft <= 7 ? 'warning' : 'success'));
        }

        if ($pengajuanDana) {
            $stats[] = Stat::make('Status Pengajuan', $this->getStatusLabel($pengajuanDana->status))
                ->description($pengajuanDana->status === LaporanStatus::REVISI ? 'Perlu diperbaiki' : 'Pengajuan tersedia')
                ->descriptionIcon($this->getStatusIcon($pengajuanDana->status))
                ->color($this->getStatusColor($pengajuanDana->status));

            if ($pengajuanDana->status === LaporanStatus::REVISI && $pengajuanDana->pesan_revisi) {
                $stats[] = Stat::make('Pesan Revisi', substr($pengajuanDana->pesan_revisi, 0, 50) . '...')
                    ->description('Klik untuk detail lengkap')
                    ->descriptionIcon('heroicon-m-chat-bubble-left-ellipsis')
                    ->color('warning');
            }

            $stats[] = Stat::make('Nominal Pengajuan', 'Rp ' . number_format($pengajuanDana->nominal, 0, ',', '.'))
                ->description('Dana yang diajukan')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('info');

            if ($pengajuanDana->status === LaporanStatus::DITERIMA) {
                $stats[] = Stat::make('Status Dana', 'Disetujui')
                    ->description('Diterima pada: ' . $pengajuanDana->accepted_at?->format('d M Y'))
                    ->descriptionIcon('heroicon-m-check-circle')
                    ->color('success');
            }

            if ($pengajuanDana->penjelasan) {
                $stats[] = Stat::make('Keterangan', substr($pengajuanDana->penjelasan, 0, 30) . '...')
                    ->description('Detail pengajuan')
                    ->descriptionIcon('heroicon-m-document-text')
                    ->color('gray');
            }
        } else {
            $stats[] = Stat::make('Status Pengajuan', 'Belum Mengajukan')
                ->description($isInPeriod ? 'Opsional - ajukan jika memerlukan dana tambahan' : 'Pengajuan dana bersifat opsional')
                ->descriptionIcon('heroicon-m-information-circle')
                ->color('info');

            if ($isInPeriod) {
                $stats[] = Stat::make('Action Required', 'Buat Pengajuan')
                    ->description('Pengajuan dana diperlukan')
                    ->descriptionIcon('heroicon-m-document-plus')
                    ->color('danger')
                    ->url(route('filament.admin.resources.pengajuan-danas.create'));
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
}
