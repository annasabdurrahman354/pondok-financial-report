<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum Jabatan: string implements HasLabel, HasColor
{
    case KETUA_PONDOK = 'ketua pondok';
    case WAKIL_KETUA_PONDOK = 'wakil ketua pondok';
    case PINISEPUH_PONDOK = 'pinisepuh pondok';
    case BENDAHARA = 'bendahara';
    case SEKRETARIS = 'sekretaris';
    case GURU_PONDOK = 'guru pondok';

    public function getLabel(): string
    {
        return match ($this) {
            self::KETUA_PONDOK => 'Ketua Pondok',
            self::WAKIL_KETUA_PONDOK => 'Wakil Ketua Pondok',
            self::PINISEPUH_PONDOK => 'Pinisepuh Pondok',
            self::BENDAHARA => 'Bendahara',
            self::SEKRETARIS => 'Sekretaris',
            self::GURU_PONDOK => 'Guru Pondok',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::KETUA_PONDOK => 'success',
            self::WAKIL_KETUA_PONDOK => 'info',
            self::PINISEPUH_PONDOK => 'warning',
            self::BENDAHARA => 'danger',
            self::SEKRETARIS => 'primary',
            self::GURU_PONDOK => 'gray',
        };
    }

    public static function getOptions(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn($case) => [$case->value => $case->getLabel()])
            ->toArray();
    }
}
