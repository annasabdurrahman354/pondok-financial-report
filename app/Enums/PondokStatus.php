<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum PondokStatus: string implements HasLabel, HasColor
{
    case REGULER = 'reguler';
    case PPM = 'ppm';
    case PPPM = 'pppm';
    case BOARDING = 'boarding';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::REGULER => 'Reguler',
            self::PPM => 'PPM',
            self::PPPM => 'PPPM',
            self::BOARDING => 'Boarding',
        };
    }

    public function getColor(): string | array | null
    {
        return match ($this) {
            self::REGULER => 'success',
            self::PPM => 'warning',
            self::PPPM => 'danger',
            self::BOARDING => 'primary',
        };
    }
}
