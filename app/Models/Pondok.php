<?php

namespace App\Models;

use App\Enums\PondokStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pondok extends Model
{
    protected $table = 'pondok';

    protected $fillable = [
        'nama',
        'status',
        'nomor_telepon',
        'alamat_lengkap',
        'provinsi',
        'kota',
        'kecamatan',
        'kelurahan',
        'kode_pos',
        'daerah_sambung',
    ];

    protected $casts = [
        'status' => PondokStatus::class,
    ];

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function pengurusPondok(): HasMany
    {
        return $this->hasMany(PengurusPondok::class);
    }

    public function rab(): HasMany
    {
        return $this->hasMany(Rab::class);
    }

    public function lpj(): HasMany
    {
        return $this->hasMany(Lpj::class);
    }
}

