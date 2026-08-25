<?php

namespace App\Filament\Resources\Roles\Tables;

use App\Models\User;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Spatie\Permission\Models\Role;

class RolesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('users_count')->counts('users')->label('Users'),
                TextColumn::make('permissions_count')->counts('permissions')->label('Permissions'),
                TextColumn::make('created_at')->dateTime()->sortable(),
            ])
            ->defaultSort('name')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->visible(fn (Role $record) => ! in_array($record->name, User::PANEL_ROLES, true)),
            ]);
    }
}
