<?php

namespace App\Models;

use App\Enums\LaporanStatus;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PengajuanDana extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_dana';

    protected $fillable = [
        'pondok_id',
        'periode_id',
        'nominal',
        'penjelasan',
        'berkas',
        'status',
        'accepted_at',
        'pesan_revisi',
    ];

    protected $casts = [
        'nominal' => 'integer',
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

                return 'Pengajuan ' . $pondok . ' '. \Carbon\Carbon::createFromFormat('m', $month)->format('F').' '.$year;
            }
        );
    }

    /**
     * Get the pondok that owns the Pengajuan Dana.
     */
    public function pondok(): BelongsTo
    {
        return $this->belongsTo(Pondok::class);
    }

    /**
     * Get the period that owns the Pengajuan Dana.
     */
    public function periode(): BelongsTo
    {
        return $this->belongsTo(Periode::class, 'periode_id', 'id');
    }


    /**
     * Check if Pengajuan Dana is drafted
     */
    public function isDrafted(): bool
    {
        return $this->status == LaporanStatus::DRAFT;
    }

    /**
     * Check if Pengajuan Dana is submitted
     */
    public function isSubmitted(): bool
    {
        return $this->status == LaporanStatus::DIAJUKAN;
    }

    /**
     * Check if Pengajuan Dana is accepted
     */
    public function isAccepted(): bool
    {
        return $this->status == LaporanStatus::DITERIMA;
    }

    /**
     * Check if Pengajuan Dana needs revision
     */
    public function needsRevision(): bool
    {
        return $this->status == LaporanStatus::REVISI;
    }

    /**
     * Submit the Pengajuan Dana
     */
    public function submit(): void
    {
        $this->update([
            'status' => 'diajukan',
        ]);
    }

    /**
     * Accept the Pengajuan Dana
     */
    public function accept(): void
    {
        $this->update([
            'accepted_at' => now(),
            'status' => 'diterima',
        ]);
    }

    /**
     * Request revision for the Pengajuan Dana
     */
    public function requestRevision(string $message): void
    {
        $this->update([
            'status' => 'revisi',
            'pesan_revisi' => $message,
            'accepted_at' => null,
        ]);
    }

    // Accessor for formatted nominal
    public function getFormattedNominalAttribute(): string
    {
        return 'Rp ' . number_format($this->nominal, 0, ',', '.');
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

    // Scope for filtering by status
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Scope for filtering by pondok
    public function scopeByPondok($query, $pondokId)
    {
        return $query->where('pondok_id', $pondokId);
    }
}
