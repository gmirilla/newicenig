<?php

namespace App\Filament\Resources\FailedJobs;

use App\Filament\Concerns\RestrictedToSuperAdmins;
use App\Filament\Resources\FailedJobs\Pages\ManageFailedJobs;
use App\Models\FailedJob;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Artisan;

class FailedJobResource extends Resource
{
    use RestrictedToSuperAdmins;

    protected static ?string $model = FailedJob::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Failed Jobs';

    protected static ?int $navigationSort = 6;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Textarea::make('exception')->rows(20)->disabled()->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('#')->sortable(),
                TextColumn::make('queue')->badge(),
                TextColumn::make('job_class')->label('Job')->state(fn (FailedJob $record) => $record->jobClass() ?? '—'),
                TextColumn::make('exception_summary')->label('Exception')->state(fn (FailedJob $record) => $record->exceptionSummary())->limit(60),
                TextColumn::make('failed_at')->dateTime()->sortable(),
            ])
            ->defaultSort('failed_at', 'desc')
            ->emptyStateHeading('No failed jobs')
            ->emptyStateDescription('Everything has processed successfully.')
            ->recordActions([
                Action::make('view')
                    ->label('View exception')
                    ->icon(Heroicon::OutlinedEye)
                    ->schema([
                        Textarea::make('exception')->rows(20)->disabled()->columnSpanFull(),
                    ])
                    ->fillForm(fn (FailedJob $record) => ['exception' => $record->exception])
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close'),
                Action::make('retry')
                    ->label('Retry')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (FailedJob $record) {
                        Artisan::call('queue:retry', ['id' => [$record->uuid]]);

                        Notification::make()->title('Job re-queued')->success()->send();
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('retry')
                        ->label('Retry selected')
                        ->icon(Heroicon::OutlinedArrowPath)
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            Artisan::call('queue:retry', ['id' => $records->pluck('uuid')->all()]);

                            Notification::make()->title('Jobs re-queued')->success()->send();
                        }),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageFailedJobs::route('/'),
        ];
    }
}
