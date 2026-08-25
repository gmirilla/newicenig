<?php

namespace App\Filament\Resources\Roles\Schemas;

use App\Models\User;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Spatie\Permission\Models\Role;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->components([
                        TextInput::make('name')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->disabled(fn (?Role $record) => $record && in_array($record->name, User::PANEL_ROLES, true))
                            ->helperText(fn (?Role $record) => $record && in_array($record->name, User::PANEL_ROLES, true)
                                ? 'This role gates admin panel access by name and cannot be renamed.'
                                : null),
                    ]),
                Section::make('Permissions')
                    ->description('Optional — attach granular permissions to this role for future use. Admin panel access is currently controlled by role name alone.')
                    ->components([
                        CheckboxList::make('permissions')
                            ->relationship('permissions', 'name')
                            ->columns(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
