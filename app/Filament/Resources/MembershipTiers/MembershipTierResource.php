<?php

namespace App\Filament\Resources\MembershipTiers;

use App\Filament\Concerns\RestrictedToAdmins;
use App\Filament\Resources\MembershipTiers\Pages\CreateMembershipTier;
use App\Filament\Resources\MembershipTiers\Pages\EditMembershipTier;
use App\Filament\Resources\MembershipTiers\Pages\ListMembershipTiers;
use App\Filament\Resources\MembershipTiers\Schemas\MembershipTierForm;
use App\Filament\Resources\MembershipTiers\Tables\MembershipTiersTable;
use App\Models\MembershipTier;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MembershipTierResource extends Resource
{
    use RestrictedToAdmins;

    protected static ?string $model = MembershipTier::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static string|\UnitEnum|null $navigationGroup = 'Membership';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return MembershipTierForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MembershipTiersTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMembershipTiers::route('/'),
            'create' => CreateMembershipTier::route('/create'),
            'edit' => EditMembershipTier::route('/{record}/edit'),
        ];
    }
}
