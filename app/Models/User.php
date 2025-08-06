<?php

namespace App\Models;

use BezhanSalleh\FilamentShield\Traits\HasPanelShield;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser, HasName
{
    use HasPanelShield, HasRoles;
    use Notifiable;

    protected $fillable = [
        'nama',
        'email',
        'nomor_telepon',
        'password',
        'pondok_id',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function getFilamentName(): string
    {
        return $this->nama;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return $this->hasRole([\App\Enums\Role::SUPER_ADMIN->value, \App\Enums\Role::ADMIN_PUSAT->value, \App\Enums\Role::ADMIN_PONDOK->value]);
    }

    public function pondok(): BelongsTo
    {
        return $this->belongsTo(Pondok::class);
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasRole(\App\Enums\Role::SUPER_ADMIN);
    }

    public function isAdminPusat(): bool
    {
        if ($this->hasRole(\App\Enums\Role::SUPER_ADMIN)) {
            return true;
        }

        return $this->hasRole(\App\Enums\Role::ADMIN_PUSAT);
    }

    public function isAdminPondok(): bool
    {
        return $this->hasRole(\App\Enums\Role::ADMIN_PONDOK);
    }

    protected static function booted()
    {
        static::created(function ($user) {
            if ($user->pondok_id) {
                $user->assignRole(\App\Enums\Role::ADMIN_PONDOK);
            }
        });
    }

}
