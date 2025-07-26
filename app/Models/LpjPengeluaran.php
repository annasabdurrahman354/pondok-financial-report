<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LpjPengeluaran extends Model
{
    protected $table = 'lpj_pengeluaran';

    protected $fillable = [
        'lpj_id',
        'kategori_pengeluaran_id',
        'rab_pengeluaran_id',
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
     * Get the LPJ that owns the pengeluaran.
     */
    public function lpj(): BelongsTo
    {
        return $this->belongsTo(Lpj::class);
    }

    /**
     * Get the kategori pengeluaran.
     */
    public function kategoriPengeluaran(): BelongsTo
    {
        return $this->belongsTo(KategoriPengeluaran::class);
    }

    /**
     * Get the related RAB pengeluaran (can be nullable).
     */
    public function rabPengeluaran()
    {
        return $this->belongsTo(RabPengeluaran::class);
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

