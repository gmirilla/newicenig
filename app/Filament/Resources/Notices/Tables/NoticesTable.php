<?php

namespace App\Filament\Resources\Notices\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NoticesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->sortable(),
                IconColumn::make('is_pinned')->boolean()->label('Pinned'),
                IconColumn::make('is_published')->boolean()->label('Published'),
                TextColumn::make('membershipTiers.name')->badge()->label('Tiers')->placeholder('All members'),
                TextColumn::make('published_at')->dateTime()->placeholder('—')->sortable(),
                TextColumn::make('expires_at')->dateTime()->placeholder('—')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
