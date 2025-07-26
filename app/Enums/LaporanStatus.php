<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum LaporanStatus: string implements HasLabel, HasColor, HasIcon
{
    case DRAFT = 'draft';
    case DIAJUKAN = 'diajukan';
    case DITERIMA = 'diterima';
    case REVISI = 'revisi';

    public function getLabel(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::DIAJUKAN => 'Diajukan',
            self::DITERIMA => 'Diterima',
            self::REVISI => 'Revisi',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::DRAFT => 'gray',
            self::DIAJUKAN => 'warning',
            self::DITERIMA => 'success',
            self::REVISI => 'danger',
        };
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::DRAFT => 'heroicon-o-document',
            self::DIAJUKAN => 'heroicon-o-paper-airplane',
            self::DITERIMA => 'heroicon-o-check-circle',
            self::REVISI => 'heroicon-o-arrow-path',
        };
    }
}
