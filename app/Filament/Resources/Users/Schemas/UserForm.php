<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->components([
                        TextInput::make('name')->required()->maxLength(255),
                        TextInput::make('email')->email()->required()->unique(ignoreRecord: true)->maxLength(255),
                        TextInput::make('phone')->tel()->maxLength(50),
                    ]),
                Section::make('Roles')
                    ->description('Controls what this user can access in the admin panel. Membership status is separate and lives under Membership → User Memberships.')
                    ->components([
                        CheckboxList::make('roles')
                            ->relationship('roles', 'name')
                            ->columns(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
