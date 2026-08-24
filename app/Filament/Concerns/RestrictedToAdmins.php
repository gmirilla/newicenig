<?php

namespace App\Filament\Concerns;

trait RestrictedToAdmins
{
    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super-admin', 'admin']) ?? false;
    }
}
