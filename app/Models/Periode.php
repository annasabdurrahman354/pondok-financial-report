<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Periode extends Model
{
    protected $table = 'periode';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'batas_awal_rab',
        'batas_akhir_rab',
        'batas_awal_lpj',
        'batas_akhir_lpj',
    ];

    public function rabs()
    {
        return $this->hasMany(Rab::class, 'periode_id', 'id');
    }

    public function lpjs()
    {
        return $this->hasMany(Lpj::class, 'periode_id', 'id');
    }

    /**
     * Get active periode for LPJ.
     */
    public static function getPeriodeLpjAktif()
    {
        $today = Carbon::today();

        return self::where('batas_awal_lpj', '<=', $today)
            ->where('batas_akhir_lpj', '>=', $today)
            ->first();
    }

    /**
     * Get active periode for RAB.
     */
    public static function getPeriodeRabAktif()
    {
        $today = Carbon::today();

        return self::where('batas_awal_rab', '<=', $today)
            ->where('batas_akhir_rab', '>=', $today)
            ->first();
    }

    /**
     * Get LPJ periode for the month before the active LPJ periode.
     */
    public static function getPeriodeLpjBulanSebelumAktif()
    {
        $active = self::getPeriodeLpjAktif();

        if ($active) {
            $previousId = Carbon::createFromFormat('Ym', $active->id)
                ->subMonth()
                ->format('Ym');

            return self::find($previousId);
        }

        return null;
    }

    /**
     * Get RAB periode for the month before the active RAB periode.
     */
    public static function getPeriodeRabBulanSebelumAktif()
    {
        $active = self::getPeriodeRabAktif();

        if ($active) {
            $previousId = Carbon::createFromFormat('Ym', $active->id)
                ->subMonth()
                ->format('Ym');

            return self::find($previousId);
        }

        return null;
    }
}
