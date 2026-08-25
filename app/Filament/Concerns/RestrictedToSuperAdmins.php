<?php

namespace App\Filament\Concerns;

trait RestrictedToSuperAdmins
{
    public static function canViewAny(): bool
    {
        return auth()->user()?->hasRole('super-admin') ?? false;
    }
}
