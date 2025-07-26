<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriPengeluaran extends Model
{
    protected $table = 'kategori_pengeluaran';

    protected $fillable = [
        'nama',
    ];

    /**
     * Get the RAB pengeluaran for the kategori.
     */
    public function rabPengeluaran(): HasMany
    {
        return $this->hasMany(RabPengeluaran::class);
    }

    /**
     * Get the LPJ pengeluaran for the kategori.
     */
    public function lpjPengeluaran(): HasMany
    {
        return $this->hasMany(LpjPengeluaran::class);
    }
}

