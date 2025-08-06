<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use App\Models\Pondok;
use App\Models\Rab;
use App\Models\Lpj;
use App\Models\PengajuanDana;
use Carbon\Carbon;

class LaporanExport implements WithMultipleSheets
{
    protected $exportTypes;
    protected $periodeStart;
    protected $periodeEnd;
    protected $pondokIds;

    public function __construct($exportTypes, $periodeStart, $periodeEnd, $pondokIds)
    {
        $this->exportTypes = $exportTypes;
        $this->periodeStart = $periodeStart;
        $this->periodeEnd = $periodeEnd;
        $this->pondokIds = $pondokIds;
    }

    public function sheets(): array
    {
        $sheets = [];

        // Get all pondok based on selection
        if (in_array('all', $this->pondokIds)) {
            $pondoks = Pondok::all();
        } else {
            $pondoks = Pondok::whereIn('id', $this->pondokIds)->get();
        }

        foreach ($pondoks as $pondok) {
            $sheets[] = new PondokSheet($pondok, $this->exportTypes, $this->periodeStart, $this->periodeEnd);
        }

        return $sheets;
    }
}

class PondokSheet implements FromCollection, WithTitle, WithHeadings, WithStyles
{
    protected $pondok;
    protected $exportTypes;
    protected $periodeStart;
    protected $periodeEnd;

    public function __construct($pondok, $exportTypes, $periodeStart, $periodeEnd)
    {
        $this->pondok = $pondok;
        $this->exportTypes = $exportTypes;
        $this->periodeStart = $periodeStart;
        $this->periodeEnd = $periodeEnd;
    }

    public function collection()
    {
        $data = collect();

        // Generate all periods in range
        $periods = $this->generatePeriods($this->periodeStart, $this->periodeEnd);

        foreach ($periods as $periode) {
            $row = [
                'periode' => $this->formatPeriode($periode),
            ];

            // RAB Data
            if (in_array('RAB', $this->exportTypes)) {
                $rab = Rab::where('periode_id', $periode)
                    ->where('pondok_id', $this->pondok->id)
                    ->first();

                if ($rab) {
                    $row['status_rab'] = $rab->status->getLabel();
                    $row['pesan_revisi_rab'] = $rab->pesan_revisi ?? '';
                    $row['total_pemasukan_rab'] = $rab->total_pemasukan;
                    $row['total_pengeluaran_rab'] = $rab->total_pengeluaran;
                    $row['saldo_rab'] = $rab->saldo;
                } else {
                    $row['status_rab'] = 'Belum Mengajukan';
                    $row['pesan_revisi_rab'] = '';
                    $row['total_pemasukan_rab'] = 0;
                    $row['total_pengeluaran_rab'] = 0;
                    $row['saldo_rab'] = 0;
                }
            }

            // LPJ Data
            if (in_array('LPJ', $this->exportTypes)) {
                $lpj = Lpj::where('periode_id', $periode)
                    ->where('pondok_id', $this->pondok->id)
                    ->first();

                if ($lpj) {
                    $row['status_lpj'] = $lpj->status->getLabel();
                    $row['pesan_revisi_lpj'] = $lpj->pesan_revisi ?? '';
                    $row['total_rencana_pemasukan'] = $lpj->total_pemasukan_rencana;
                    $row['total_rencana_pengeluaran'] = $lpj->total_pengeluaran_rencana;
                    $row['total_realisasi_pemasukan'] = $lpj->total_pemasukan_realisasi;
                    $row['total_realisasi_pengeluaran'] = $lpj->total_pengeluaran_realisasi;
                    $row['saldo_lpj'] = $lpj->saldo_realisasi;
                } else {
                    $row['status_lpj'] = 'Belum Mengajukan';
                    $row['pesan_revisi_lpj'] = '';
                    $row['total_rencana_pemasukan'] = 0;
                    $row['total_rencana_pengeluaran'] = 0;
                    $row['total_realisasi_pemasukan'] = 0;
                    $row['total_realisasi_pengeluaran'] = 0;
                    $row['saldo_lpj'] = 0;
                }
            }

            // Pengajuan Dana Data
            if (in_array('Pengajuan Dana', $this->exportTypes)) {
                $pengajuanDana = PengajuanDana::where('periode_id', $periode)
                    ->where('pondok_id', $this->pondok->id)
                    ->first();

                if ($pengajuanDana) {
                    $row['status_pengajuan_dana'] = $pengajuanDana->status->getLabel();
                    $row['pesan_revisi_pengajuan'] = $pengajuanDana->pesan_revisi ?? '';
                    $row['nominal_pengajuan'] = $pengajuanDana->nominal;
                } else {
                    $row['status_pengajuan_dana'] = 'Tidak Mengajukan';
                    $row['pesan_revisi_pengajuan'] = '';
                    $row['nominal_pengajuan'] = 0;
                }
            }

            $data->push($row);
        }

        return $data;
    }

    public function title(): string
    {
        return $this->pondok->nama;
    }

    public function headings(): array
    {
        $headings = ['Periode'];

        if (in_array('RAB', $this->exportTypes)) {
            $headings = array_merge($headings, [
                'Status RAB',
                'Pesan Revisi RAB',
                'Total Pemasukan RAB',
                'Total Pengeluaran RAB',
                'Saldo RAB'
            ]);
        }

        if (in_array('LPJ', $this->exportTypes)) {
            $headings = array_merge($headings, [
                'Status LPJ',
                'Pesan Revisi LPJ',
                'Total Rencana Pemasukan',
                'Total Rencana Pengeluaran',
                'Total Realisasi Pemasukan',
                'Total Realisasi Pengeluaran',
                'Saldo LPJ'
            ]);
        }

        if (in_array('Pengajuan Dana', $this->exportTypes)) {
            $headings = array_merge($headings, [
                'Status Pengajuan Dana',
                'Pesan Revisi Pengajuan',
                'Nominal Pengajuan'
            ]);
        }

        return $headings;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    private function generatePeriods($start, $end)
    {
        $periods = [];
        $current = $start;

        while ($current <= $end) {
            $periods[] = $current;

            // Increment to next month
            $year = intval(substr($current, 0, 4));
            $month = intval(substr($current, 4, 2));

            $month++;
            if ($month > 12) {
                $month = 1;
                $year++;
            }

            $current = $year . str_pad($month, 2, '0', STR_PAD_LEFT);
        }

        return $periods;
    }

    private function formatPeriode($periode)
    {
        $year = substr($periode, 0, 4);
        $month = substr($periode, 4, 2);
        return Carbon::createFromFormat('m', $month)->format('F') . ' ' . $year;
    }
}
