<?php

namespace App\Models;

use App\Enums\LaporanStatus;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Carbon\Carbon;

class Lpj extends Model
{
    protected $table = 'lpj';

    protected $fillable = [
        'pondok_id',
        'periode_id',
        'status',
        'accepted_at',
        'pesan_revisi',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
        'status' => LaporanStatus::class,
    ];

    protected function recordTitle(): Attribute
    {
        return Attribute::make(
            get: function () {
                $year = substr($this->periode_id, 0, 4);
                $month = substr($this->periode_id, 4, 2);

                $pondok = auth()->user()->isAdminPusat() ? $this->pondok->nama : '';

                return 'LPJ ' . \Carbon\Carbon::createFromFormat('m', $month)->format('F').' '.$year . ' ' . $pondok;
            }
        );
    }

    /**
     * Get the pondok that owns the LPJ.
     */
    public function pondok(): BelongsTo
    {
        return $this->belongsTo(Pondok::class);
    }

    /**
     * Get the pemasukan for the LPJ.
     */
    public function pemasukan(): HasMany
    {
        return $this->hasMany(LpjPemasukan::class);
    }

    /**
     * Get the pengeluaran for the LPJ.
     */
    public function pengeluaran(): HasMany
    {
        return $this->hasMany(LpjPengeluaran::class);
    }

    /**
     * Get the RAB for this LPJ period.
     */
    public function rab(): HasMany
    {
        return $this->hasMany(Rab::class, 'periode_id', 'periode_id')
                    ->where('pondok_id', $this->pondok_id);
    }

    /**
     * Get total pemasukan rencana
     */
    public function getTotalPemasukanRencanaAttribute(): int
    {
        return $this->pemasukan()->sum('nominal_rencana');
    }

    /**
     * Get total pemasukan realisasi
     */
    public function getTotalPemasukanRealisasiAttribute(): int
    {
        return $this->pemasukan()->sum('nominal_realisasi');
    }

    /**
     * Get total pengeluaran rencana
     */
    public function getTotalPengeluaranRencanaAttribute(): int
    {
        return $this->pengeluaran()->sum('nominal_rencana');
    }

    /**
     * Get total pengeluaran realisasi
     */
    public function getTotalPengeluaranRealisasiAttribute(): int
    {
        return $this->pengeluaran()->sum('nominal_realisasi');
    }

    /**
     * Get saldo rencana
     */
    public function getSaldoRencanaAttribute(): int
    {
        return $this->total_pemasukan_rencana - $this->total_pengeluaran_rencana;
    }

    /**
     * Get saldo realisasi
     */
    public function getSaldoRealisasiAttribute(): int
    {
        return $this->total_pemasukan_realisasi - $this->total_pengeluaran_realisasi;
    }

    /**
     * Get formatted periode
     */
    public function getFormattedPeriodeAttribute(): string
    {
        $year = substr($this->periode_id, 0, 4);
        $month = substr($this->periode_id, 4, 2);
        $monthName = Carbon::createFromFormat('m', $month)->format('F');
        return $monthName . ' ' . $year;
    }

    /**
     * Check if LPJ is drafted
     */
    public function isDrafted(): bool
    {
        return $this->status == LaporanStatus::DRAFT;
    }

    /**
     * Check if LPJ is submitted
     */
    public function isSubmitted(): bool
    {
        return $this->status == LaporanStatus::DIAJUKAN;
    }

    /**
     * Check if LPJ is accepted
     */
    public function isAccepted(): bool
    {
        return $this->status == LaporanStatus::DITERIMA;
    }

    /**
     * Check if LPJ needs revision
     */
    public function needsRevision(): bool
    {
        return $this->status == LaporanStatus::REVISI;
    }

    /**
     * Submit the LPJ
     */
    public function submit(): void
    {
        $this->update([
            'status' => 'diajukan',
        ]);
    }

    /**
     * Accept the LPJ
     */
    public function accept(): void
    {
        $this->update([
            'accepted_at' => now(),
            'status' => 'diterima',
        ]);
    }

    /**
     * Request revision for the LPJ
     */
    public function requestRevision(string $message): void
    {
        $this->update([
            'status' => 'revisi',
            'pesan_revisi' => $message,
            'accepted_at' => null,
        ]);
    }
}

