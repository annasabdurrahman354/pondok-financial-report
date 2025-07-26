<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RabPengeluaran extends Model
{
    protected $table = 'rab_pengeluaran';

    protected $fillable = [
        'rab_id',
        'kategori_pengeluaran_id',
        'nama',
        'detail',
        'nominal',
    ];

    protected $casts = [
        'nominal' => 'integer',
    ];

    /**
     * Get the RAB that owns the pengeluaran.
     */
    public function rab(): BelongsTo
    {
        return $this->belongsTo(Rab::class);
    }

    /**
     * Get the kategori pengeluaran.
     */
    public function kategoriPengeluaran(): BelongsTo
    {
        return $this->belongsTo(KategoriPengeluaran::class);
    }

    /**
     * Get formatted nominal
     */
    public function getFormattedNominalAttribute(): string
    {
        return 'Rp ' . number_format($this->nominal, 0, ',', '.');
    }
}

