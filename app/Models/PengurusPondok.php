<?php

namespace App\Models;

use App\Enums\Jabatan;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PengurusPondok extends Model
{
    protected $table = 'pengurus_pondok';

    protected $fillable = [
        'pondok_id',
        'nama',
        'nomor_telepon',
        'jabatan',
    ];

    protected $casts = [
        'jabatan' => Jabatan::class,
    ];

    public function pondok(): BelongsTo
    {
        return $this->belongsTo(Pondok::class);
    }

    public function isKetuaPondok(): bool
    {
        return $this->jabatan == Jabatan::KETUA_PONDOK;
    }
}

