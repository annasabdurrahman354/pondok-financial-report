<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RabPemasukan extends Model
{
    protected $table = 'rab_pemasukan';

    protected $fillable = [
        'rab_id',
        'kategori_pemasukan_id',
        'nama',
        'detail',
        'nominal',
    ];

    protected $casts = [
        'nominal' => 'integer',
    ];

    /**
     * Get the RAB that owns the pemasukan.
     */
    public function rab(): BelongsTo
    {
        return $this->belongsTo(Rab::class);
    }

    /**
     * Get the kategori pemasukan.
     */
    public function kategoriPemasukan(): BelongsTo
    {
        return $this->belongsTo(KategoriPemasukan::class);
    }

    /**
     * Check if this is sisa saldo
     */
    public function isSisaSaldo(): bool
    {
        return $this->kategori_pemasukan_id === 1;
    }

    /**
     * Get formatted nominal
     */
    public function getFormattedNominalAttribute(): string
    {
        return 'Rp ' . number_format($this->nominal, 0, ',', '.');
    }
}

