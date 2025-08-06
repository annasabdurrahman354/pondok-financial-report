<?php

namespace App\Filament\Resources\LpjResource\Widgets;

use App\Models\Lpj;
use App\Models\Pondok;
use App\Models\Periode;
use App\Enums\LaporanStatus;
use Carbon\Carbon;
use Filament\Widgets\StatsOverviewWidget as BaseStatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Auth;

class LpjStatsWidget extends BaseStatsOverviewWidget
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
        $periode = Periode::getPeriodeLpjAktif();
        $isActivePeriod = $periode !== null;

        if (!$periode) {
            $periode = Periode::orderBy('id', 'desc')->first();
        }

        if (!$periode) {
            return [
                Stat::make('Status', 'Tidak ada periode LPJ')
                    ->description('Silakan buat periode LPJ terlebih dahulu')
                    ->color('warning')
            ];
        }

        $lpjs = Lpj::where('periode_id', $periode->id)->get();
        $totalPemasukanRencana = $lpjs->sum('total_pemasukan_rencana');
        $totalPemasukanRealisasi = $lpjs->sum('total_pemasukan_realisasi');
        $totalPengeluaranRencana = $lpjs->sum('total_pengeluaran_rencana');
        $totalPengeluaranRealisasi = $lpjs->sum('total_pengeluaran_realisasi');
        $saldoRencana = $totalPemasukanRencana - $totalPengeluaranRencana;
        $saldoRealisasi = $totalPemasukanRealisasi - $totalPengeluaranRealisasi;

        $allPondok = Pondok::count();
        $pondokFilled = $lpjs->pluck('pondok_id')->unique()->count();
        $pondokSubmitted = $lpjs->whereIn('status', [LaporanStatus::DIAJUKAN, LaporanStatus::DITERIMA])->pluck('pondok_id')->unique()->count();
        $pondokAccepted = $lpjs->where('status', LaporanStatus::DITERIMA)->pluck('pondok_id')->unique()->count();
        $pondokNeedsRevision = $lpjs->where('status', LaporanStatus::REVISI)->pluck('pondok_id')->unique()->count();
        $pondokNotFilled = $allPondok - $pondokFilled;

        // Calculate variance percentages
        $pemasukanVariance = $totalPemasukanRencana > 0 ? (($totalPemasukanRealisasi - $totalPemasukanRencana) / $totalPemasukanRencana) * 100 : 0;
        $pengeluaranVariance = $totalPengeluaranRencana > 0 ? (($totalPengeluaranRealisasi - $totalPengeluaranRencana) / $totalPengeluaranRencana) * 100 : 0;

        $stats = [
            Stat::make('Pemasukan Realisasi', 'Rp ' . number_format($totalPemasukanRealisasi, 0, ',', '.'))
                ->description(sprintf('vs Rencana: %+.1f%%', $pemasukanVariance))
                ->descriptionIcon($pemasukanVariance >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($pemasukanVariance >= 0 ? 'success' : 'warning'),

            Stat::make('Pengeluaran Realisasi', 'Rp ' . number_format($totalPengeluaranRealisasi, 0, ',', '.'))
                ->description(sprintf('vs Rencana: %+.1f%%', $pengeluaranVariance))
                ->descriptionIcon($pengeluaranVariance <= 0 ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-arrow-trending-up')
                ->color($pengeluaranVariance <= 10 ? 'success' : ($pengeluaranVariance <= 20 ? 'warning' : 'danger')),

            Stat::make('Saldo Realisasi', 'Rp ' . number_format($saldoRealisasi, 0, ',', '.'))
                ->description($saldoRealisasi >= 0 ? 'Surplus' : 'Defisit')
                ->descriptionIcon($saldoRealisasi >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($saldoRealisasi >= 0 ? 'success' : 'danger'),

            Stat::make('Akurasi Budget', sprintf('%.1f%%', $totalPengeluaranRencana > 0 ? (100 - abs($pengeluaranVariance)) : 0))
                ->description('Ketepatan realisasi pengeluaran')
                ->descriptionIcon('heroicon-m-chart-bar')
                ->color(abs($pengeluaranVariance) <= 10 ? 'success' : (abs($pengeluaranVariance) <= 20 ? 'warning' : 'danger')),

            Stat::make('Pondok Sudah Isi', $pondokFilled . ' / ' . $allPondok)
                ->description(sprintf('%.1f%% dari total pondok', ($pondokFilled / max($allPondok, 1)) * 100))
                ->descriptionIcon($pondokFilled == $allPondok ? 'heroicon-m-check-circle' : 'heroicon-m-users')
                ->color($pondokFilled == $allPondok ? 'success' : ($pondokFilled >= $allPondok * 0.8 ? 'warning' : 'danger')),

            Stat::make('Status Pengajuan', $pondokSubmitted . ' diajukan')
                ->description($pondokAccepted . ' diterima, ' . $pondokNeedsRevision . ' revisi')
                ->descriptionIcon($pondokAccepted == $pondokSubmitted ? 'heroicon-m-check-badge' : 'heroicon-m-clock')
                ->color($pondokAccepted == $pondokSubmitted && $pondokSubmitted > 0 ? 'success' : 'warning'),
        ];

        if (!$isActivePeriod) {
            $stats[] = Stat::make('Status Periode', 'Tidak Aktif')
                ->description('Periode: ' . Carbon::parse($periode->batas_awal_lpj)->format('d M') . ' - ' . Carbon::parse($periode->batas_akhir_lpj)->format('d M Y'))
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning');
        } else {
            $daysLeft = Carbon::parse($periode->batas_akhir_lpj)->diffInDays(Carbon::now());
            $stats[] = Stat::make('Periode Aktif', $daysLeft . ' hari tersisa')
                ->description('Berakhir: ' . Carbon::parse($periode->batas_akhir_lpj)->format('d M Y'))
                ->descriptionIcon('heroicon-m-clock')
                ->color($daysLeft <= 3 ? 'danger' : ($daysLeft <= 7 ? 'warning' : 'success'));
        }

        if ($pondokNotFilled > 0) {
            $stats[] = Stat::make('Belum Mengisi', $pondokNotFilled . ' pondok')
                ->description('Memerlukan tindak lanjut segera')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color('danger');
        } else {
            // If all pondok filled, show completion rate instead
            $completionRate = $pondokFilled > 0 ? ($pondokAccepted / $pondokFilled) * 100 : 0;
            $stats[] = Stat::make('Tingkat Penyelesaian', sprintf('%.1f%%', $completionRate))
                ->description('LPJ yang telah diterima')
                ->descriptionIcon('heroicon-m-academic-cap')
                ->color($completionRate >= 90 ? 'success' : ($completionRate >= 70 ? 'warning' : 'danger'));
        }

        return $stats;
    }

    private function getAdminPondokStats(): array
    {
        $user = Auth::user();
        $periode = Periode::getPeriodeLpjAktif();
        $isInPeriod = $periode !== null;

        if (!$periode) {
            $periode = Periode::orderBy('id', 'desc')->first();
        }

        if (!$periode) {
            return [
                Stat::make('Status', 'Tidak ada periode LPJ')
                    ->description('Menunggu periode LPJ dibuka')
                    ->color('warning')
            ];
        }

        $lpj = Lpj::where('pondok_id', $user->pondok_id)
            ->where('periode_id', $periode->id)
            ->first();

        // Get RAB for comparison
        $rab = $lpj ? $lpj->rab()->first() : null;

        $stats = [];

        if (!$isInPeriod) {
            $stats[] = Stat::make('Periode LPJ', 'Belum Aktif')
                ->description('Mulai: ' . Carbon::parse($periode->batas_awal_lpj)->format('d M Y'))
                ->descriptionIcon('heroicon-m-calendar')
                ->color('warning');

            $stats[] = Stat::make('Berakhir', Carbon::parse($periode->batas_akhir_lpj)->format('d M Y'))
                ->description($periode->formatted_periode)
                ->descriptionIcon('heroicon-m-clock')
                ->color('info');
        } else {
            $daysLeft = Carbon::parse($periode->batas_akhir_lpj)->diffInDays(Carbon::now());
            $stats[] = Stat::make('Periode Aktif', $daysLeft . ' hari tersisa')
                ->description('Berakhir: ' . Carbon::parse($periode->batas_akhir_lpj)->format('d M Y'))
                ->descriptionIcon('heroicon-m-clock')
                ->color($daysLeft <= 3 ? 'danger' : ($daysLeft <= 7 ? 'warning' : 'success'));
        }

        if ($lpj) {
            $stats[] = Stat::make('Status LPJ', $this->getStatusLabel($lpj->status))
                ->description($lpj->status === LaporanStatus::REVISI ? 'Perlu diperbaiki' : 'LPJ tersedia')
                ->descriptionIcon($this->getStatusIcon($lpj->status))
                ->color($this->getStatusColor($lpj->status));

            $stats[] = Stat::make('Pemasukan Realisasi', 'Rp ' . number_format($lpj->total_pemasukan_realisasi, 0, ',', '.'))
                ->description('vs Rencana: Rp ' . number_format($lpj->total_pemasukan_rencana, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-arrow-up')
                ->color('success');

            $stats[] = Stat::make('Pengeluaran Realisasi', 'Rp ' . number_format($lpj->total_pengeluaran_realisasi, 0, ',', '.'))
                ->description('vs Rencana: Rp ' . number_format($lpj->total_pengeluaran_rencana, 0, ',', '.'))
                ->descriptionIcon('heroicon-m-arrow-down')
                ->color('danger');

            $saldoRealisasi = $lpj->saldo_realisasi;
            $stats[] = Stat::make('Saldo Realisasi', 'Rp ' . number_format($saldoRealisasi, 0, ',', '.'))
                ->description($saldoRealisasi >= 0 ? 'Surplus' : 'Defisit')
                ->descriptionIcon($saldoRealisasi >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color($saldoRealisasi >= 0 ? 'success' : 'danger');

            // Variance analysis
            $pemasukanVariance = $lpj->total_pemasukan_rencana > 0 ?
                (($lpj->total_pemasukan_realisasi - $lpj->total_pemasukan_rencana) / $lpj->total_pemasukan_rencana) * 100 : 0;

            $stats[] = Stat::make('Varians Pemasukan', sprintf('%+.1f%%', $pemasukanVariance))
                ->description('Realisasi vs Rencana')
                ->descriptionIcon($pemasukanVariance >= 0 ? 'heroicon-m-arrow-trending-up' : 'heroicon-m-arrow-trending-down')
                ->color(abs($pemasukanVariance) <= 10 ? 'success' : (abs($pemasukanVariance) <= 20 ? 'warning' : 'danger'));

            $pengeluaranVariance = $lpj->total_pengeluaran_rencana > 0 ?
                (($lpj->total_pengeluaran_realisasi - $lpj->total_pengeluaran_rencana) / $lpj->total_pengeluaran_rencana) * 100 : 0;

            $stats[] = Stat::make('Varians Pengeluaran', sprintf('%+.1f%%', $pengeluaranVariance))
                ->description('Realisasi vs Rencana')
                ->descriptionIcon($pengeluaranVariance <= 0 ? 'heroicon-m-arrow-trending-down' : 'heroicon-m-arrow-trending-up')
                ->color(abs($pengeluaranVariance) <= 10 ? 'success' : (abs($pengeluaranVariance) <= 20 ? 'warning' : 'danger'));

            if ($lpj->status === LaporanStatus::REVISI && $lpj->pesan_revisi) {
                $stats[] = Stat::make('Pesan Revisi', substr($lpj->pesan_revisi, 0, 50) . '...')
                    ->description('Klik untuk detail lengkap')
                    ->descriptionIcon('heroicon-m-chat-bubble-left-ellipsis')
                    ->color('warning');
            } elseif ($lpj->accepted_at) {
                $stats[] = Stat::make('Diterima', Carbon::parse($lpj->accepted_at)->format('d M Y'))
                    ->description('LPJ telah disetujui')
                    ->descriptionIcon('heroicon-m-check-badge')
                    ->color('success');
            } else {
                // Show budget efficiency if no revision message
                $efficiency = $lpj->total_pengeluaran_rencana > 0 ?
                    (($lpj->total_pengeluaran_rencana - $lpj->total_pengeluaran_realisasi) / $lpj->total_pengeluaran_rencana) * 100 : 0;

                $stats[] = Stat::make('Efisiensi Budget', sprintf('%.1f%%', $efficiency))
                    ->description($efficiency >= 0 ? 'Hemat dari rencana' : 'Melebihi rencana')
                    ->descriptionIcon($efficiency >= 0 ? 'heroicon-m-currency-dollar' : 'heroicon-m-exclamation-triangle')
                    ->color($efficiency >= 0 ? 'success' : 'warning');
            }
        } else {
            $stats[] = Stat::make('Status LPJ', 'Belum Mengisi')
                ->description($isInPeriod ? 'Segera isi sebelum batas waktu' : 'Menunggu periode aktif')
                ->descriptionIcon('heroicon-m-exclamation-circle')
                ->color($isInPeriod ? 'danger' : 'warning');

            if ($isInPeriod) {
                $stats[] = Stat::make('Action Required', 'Isi LPJ')
                    ->description('Data LPJ diperlukan')
                    ->descriptionIcon('heroicon-m-document-plus')
                    ->color('danger')
                    ->url(route('filament.admin.resources.lpjs.create'));
            } else {
                $stats[] = Stat::make('Persiapan', 'Tunggu Periode Aktif')
                    ->description('Siapkan dokumen LPJ')
                    ->descriptionIcon('heroicon-m-document-text')
                    ->color('info');
            }

            // Fill remaining stats with placeholder/info stats
            $stats[] = Stat::make('RAB Periode Ini', $rab ? 'Tersedia' : 'Tidak Ada')
                ->description($rab ? 'Rp ' . number_format($rab->total_pemasukan, 0, ',', '.') . ' rencana' : 'Perlu RAB untuk referensi')
                ->descriptionIcon('heroicon-m-document-chart-bar')
                ->color($rab ? 'info' : 'warning');

            $stats[] = Stat::make('Panduan', 'Siapkan Bukti')
                ->description('Kumpulkan dokumen pendukung')
                ->descriptionIcon('heroicon-m-folder')
                ->color('info');

            $stats[] = Stat::make('Reminder', 'Cek Kelengkapan')
                ->description('Pastikan semua data lengkap')
                ->descriptionIcon('heroicon-m-clipboard-document-check')
                ->color('info');

            $stats[] = Stat::make('Tips', 'Input Realisasi')
                ->description('Masukkan data aktual pengeluaran')
                ->descriptionIcon('heroicon-m-light-bulb')
                ->color('info');
        }

        // Ensure we always have 8 stats by adding padding if needed
        while (count($stats) < 8) {
            $stats[] = Stat::make('Info', 'Data tidak tersedia')
                ->description('Periode belum aktif')
                ->descriptionIcon('heroicon-m-information-circle')
                ->color('gray');
        }

        return array_slice($stats, 0, 8); // Ensure exactly 8 stats
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
        $periode = Periode::getPeriodeLpjAktif();

        if (!$periode) {
            return 3;
        }
        return 4; // This sets the number of columns to 4
    }
}
