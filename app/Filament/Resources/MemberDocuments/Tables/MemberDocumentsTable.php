<?php

namespace App\Filament\Resources\MemberDocuments\Tables;

use App\Models\MemberDocument;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MemberDocumentsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('user.name')->label('Member')->searchable()->sortable(),
                TextColumn::make('title')->searchable()->sortable(),
                TextColumn::make('type')->badge()->formatStateUsing(fn (string $state) => ucfirst($state)),
                TextColumn::make('uploader.name')->label('Uploaded by')->placeholder('—'),
                TextColumn::make('created_at')->dateTime()->label('Uploaded')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->options([
                        'certificate' => 'Certificate',
                        'resource' => 'Resource',
                        'other' => 'Other',
                    ]),
            ])
            ->recordActions([
                Action::make('download')
                    ->icon(Heroicon::OutlinedArrowDownTray)
                    ->url(fn (MemberDocument $record) => route('member.documents.download', $record))
                    ->openUrlInNewTab(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
