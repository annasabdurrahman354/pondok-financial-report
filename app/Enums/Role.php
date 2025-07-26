<?php
namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum Role : string implements HasLabel, HasColor {
    case SUPER_ADMIN = 'Super Admin';
    case ADMIN_PUSAT = 'Admin Pusat';
    case ADMIN_PONDOK = 'Admin Pondok';

    public function getLabel(): ?string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'Admin Pusat',
            self::ADMIN_PUSAT => 'Admin Pusat',
            self::ADMIN_PONDOK => 'Admin Pondok',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::SUPER_ADMIN => 'success',
            self::ADMIN_PUSAT => 'primary',
            self::ADMIN_PONDOK => 'secondary',
        };
    }
}
