<?php

namespace App\Filament\Resources\UserMemberships;

use App\Filament\Resources\UserMemberships\Pages\CreateUserMembership;
use App\Filament\Resources\UserMemberships\Pages\EditUserMembership;
use App\Filament\Resources\UserMemberships\Pages\ListUserMemberships;
use App\Filament\Resources\UserMemberships\Schemas\UserMembershipForm;
use App\Filament\Resources\UserMemberships\Tables\UserMembershipsTable;
use App\Models\UserMembership;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class UserMembershipResource extends Resource
{
    protected static ?string $model = UserMembership::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static string|\UnitEnum|null $navigationGroup = 'Membership';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return UserMembershipForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return UserMembershipsTable::configure($table);
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
            'index' => ListUserMemberships::route('/'),
            'create' => CreateUserMembership::route('/create'),
            'edit' => EditUserMembership::route('/{record}/edit'),
        ];
    }
}
