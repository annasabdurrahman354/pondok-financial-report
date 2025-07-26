<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriPemasukan extends Model
{
    protected $table = 'kategori_pemasukan';

    protected $fillable = [
        'nama',
    ];

    /**
     * Get the RAB pemasukan for the kategori.
     */
    public function rabPemasukan(): HasMany
    {
        return $this->hasMany(RabPemasukan::class);
    }

    /**
     * Get the LPJ pemasukan for the kategori.
     */
    public function lpjPemasukan(): HasMany
    {
        return $this->hasMany(LpjPemasukan::class);
    }

    /**
     * Check if this is the "Sisa Saldo" category
     */
    public function isSisaSaldo(): bool
    {
        return $this->id === 1;
    }
}

