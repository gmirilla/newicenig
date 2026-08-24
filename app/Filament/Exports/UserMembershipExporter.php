<?php

namespace App\Filament\Exports;

use App\Models\UserMembership;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Illuminate\Support\Str;

class UserMembershipExporter extends Exporter
{
    protected static ?string $model = UserMembership::class;

    public static function getColumns(): array
    {
        return [
            ExportColumn::make('membership_number'),
            ExportColumn::make('first_name'),
            ExportColumn::make('last_name'),
            ExportColumn::make('email'),
            ExportColumn::make('phone'),
            ExportColumn::make('membershipTier.name')->label('Tier'),
            ExportColumn::make('status'),
            ExportColumn::make('expires_at'),
            ExportColumn::make('created_at'),
        ];
    }

    public static function getCompletedNotificationBody(Export $export): string
    {
        $body = 'Your user membership export has completed and ' . Str::of('row')->counted($export->successful_rows) . ' exported.';

        if ($failedRowsCount = $export->getFailedRowsCount()) {
            $body .= ' ' . Str::of('row')->counted($failedRowsCount) . ' failed to export.';
        }

        return $body;
    }
}
