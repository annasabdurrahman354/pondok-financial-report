<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LpjPemasukan extends Model
{
    protected $table = 'lpj_pemasukan';

    protected $fillable = [
        'lpj_id',
        'kategori_pemasukan_id',
        'rab_pemasukan_id',
        'nama',
        'detail',
        'nominal_rencana',
        'nominal_realisasi',
        'keterangan_realisasi',
    ];

    protected $casts = [
        'nominal_rencana' => 'integer',
        'nominal_realisasi' => 'integer',
    ];

    /**
     * Get the LPJ that owns the pemasukan.
     */
    public function lpj(): BelongsTo
    {
        return $this->belongsTo(Lpj::class);
    }

    /**
     * Get the kategori pemasukan.
     */
    public function kategoriPemasukan(): BelongsTo
    {
        return $this->belongsTo(KategoriPemasukan::class);
    }

    /**
     * Get the related RAB pemasukan (can be nullable).
     */
    public function rabPemasukan()
    {
        return $this->belongsTo(RabPemasukan::class);
    }

    /**
     * Check if this is sisa saldo
     */
    public function isSisaSaldo(): bool
    {
        return $this->kategori_pemasukan_id === 1;
    }

    /**
     * Get variance (difference between realisasi and rencana)
     */
    public function getVarianceAttribute(): int
    {
        return $this->nominal_realisasi - $this->nominal_rencana;
    }

    /**
     * Get variance percentage
     */
    public function getVariancePercentageAttribute(): float
    {
        if ($this->nominal_rencana == 0) {
            return 0;
        }
        return ($this->variance / $this->nominal_rencana) * 100;
    }

    /**
     * Get formatted nominal rencana
     */
    public function getFormattedNominalRencanaAttribute(): string
    {
        return 'Rp ' . number_format($this->nominal_rencana, 0, ',', '.');
    }

    /**
     * Get formatted nominal realisasi
     */
    public function getFormattedNominalRealisasiAttribute(): string
    {
        return 'Rp ' . number_format($this->nominal_realisasi, 0, ',', '.');
    }

    /**
     * Get formatted variance
     */
    public function getFormattedVarianceAttribute(): string
    {
        return 'Rp ' . number_format($this->variance, 0, ',', '.');
    }
}

