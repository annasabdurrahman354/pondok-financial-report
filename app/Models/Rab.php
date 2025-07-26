<?php

namespace App\Models;

use App\Enums\LaporanStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rab extends Model
{
    protected $table = 'rab';

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

                return 'RAB ' . \Carbon\Carbon::createFromFormat('m', $month)->format('F').' '.$year . ' ' . $pondok;
            }
        );
    }

    /**
     * Get the pondok that owns the RAB.
     */
    public function pondok(): BelongsTo
    {
        return $this->belongsTo(Pondok::class);
    }

    /**
     * Get the pemasukan for the RAB.
     */
    public function pemasukan(): HasMany
    {
        return $this->hasMany(RabPemasukan::class);
    }

    /**
     * Get the pengeluaran for the RAB.
     */
    public function pengeluaran(): HasMany
    {
        return $this->hasMany(RabPengeluaran::class);
    }

    /**
     * Get the LPJ for this RAB period.
     */
    public function lpj(): HasMany
    {
        return $this->hasMany(Lpj::class, 'periode_id', 'periode_id')
            ->where('pondok_id', $this->pondok_id);
    }

    /**
     * Get total pemasukan
     */
    public function getTotalPemasukanAttribute(): int
    {
        return $this->pemasukan()->sum('nominal');
    }

    /**
     * Get total pengeluaran
     */
    public function getTotalPengeluaranAttribute(): int
    {
        return $this->pengeluaran()->sum('nominal');
    }

    /**
     * Get saldo
     */
    public function getSaldoAttribute(): int
    {
        return $this->total_pemasukan - $this->total_pengeluaran;
    }

    /**
     * Get formatted periode
     */
    public function getFormattedPeriodeAttribute(): string
    {
        $year = substr($this->periode_id, 0, 4);
        $month = substr($this->periode_id, 4, 2);
        $monthName = Carbon::createFromFormat('m', $month)->format('F');

        return $monthName.' '.$year;
    }

    /**
     * Check if RAB is drafted
     */
    public function isDrafted(): bool
    {
        return $this->status == LaporanStatus::DRAFT;
    }

    /**
     * Check if RAB is submitted
     */
    public function isSubmitted(): bool
    {
        return $this->status == LaporanStatus::DIAJUKAN;
    }

    /**
     * Check if RAB is accepted
     */
    public function isAccepted(): bool
    {
        return $this->status == LaporanStatus::DITERIMA;
    }

    /**
     * Check if RAB needs revision
     */
    public function needsRevision(): bool
    {
        return $this->status == LaporanStatus::REVISI;
    }

    /**
     * Submit the RAB
     */
    public function submit(): void
    {
        $this->update([
            'status' => 'diajukan',
        ]);
    }

    /**
     * Accept the RAB
     */
    public function accept(): void
    {
        $this->update([
            'accepted_at' => now(),
            'status' => 'diterima',
        ]);
    }

    /**
     * Request revision for the RAB
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
