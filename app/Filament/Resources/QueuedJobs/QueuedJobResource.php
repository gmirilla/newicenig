<?php

namespace App\Filament\Resources\QueuedJobs;

use App\Filament\Concerns\RestrictedToSuperAdmins;
use App\Filament\Resources\QueuedJobs\Pages\ManageQueuedJobs;
use App\Models\QueuedJob;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QueuedJobResource extends Resource
{
    use RestrictedToSuperAdmins;

    protected static ?string $model = QueuedJob::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQueueList;

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Pending Jobs';

    protected static ?int $navigationSort = 5;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('#')->sortable(),
                TextColumn::make('queue')->badge(),
                TextColumn::make('job_class')->label('Job')->state(fn (QueuedJob $record) => $record->jobClass() ?? '—'),
                TextColumn::make('attempts')->label('Attempts'),
                IconColumn::make('reserved')->label('In progress')->state(fn (QueuedJob $record) => $record->isReserved())->boolean(),
                TextColumn::make('created')->label('Queued at')->state(fn (QueuedJob $record) => $record->createdAtDate()->diffForHumans()),
            ])
            ->defaultSort('id')
            ->emptyStateHeading('No pending jobs')
            ->emptyStateDescription('The queue is empty — everything has been processed.')
            ->recordActions([
                DeleteAction::make()->label('Cancel'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('Cancel selected'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageQueuedJobs::route('/'),
        ];
    }
}
