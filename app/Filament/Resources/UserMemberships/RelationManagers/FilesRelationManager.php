<?php

namespace App\Filament\Resources\UserMemberships\RelationManagers;

use App\Models\MemberFile;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class FilesRelationManager extends RelationManager
{
    protected static string $relationship = 'files';

    protected static ?string $title = 'Uploaded documents';

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('type')
            ->columns([
                TextColumn::make('type')
                    ->label('Document')
                    ->formatStateUsing(fn (string $state) => ucwords(str_replace('_', ' ', $state))),
                TextColumn::make('created_at')->dateTime()->label('Uploaded'),
            ])
            ->recordUrl(null)
            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->url(fn (MemberFile $record) => $record->fileUrl())
                    ->openUrlInNewTab()
                    ->visible(fn (MemberFile $record) => (bool) $record->fileUrl()),
                DeleteAction::make(),
            ]);
    }
}
