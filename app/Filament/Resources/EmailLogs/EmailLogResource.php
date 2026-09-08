<?php

namespace App\Filament\Resources\EmailLogs;

use App\Enums\EmailLogStatus;
use App\Filament\Concerns\RestrictedToAdmins;
use App\Filament\Resources\EmailLogs\Pages\ManageEmailLogs;
use App\Models\EmailLog;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Artisan;

class EmailLogResource extends Resource
{
    use RestrictedToAdmins;

    protected static ?string $model = EmailLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Email Log';

    protected static ?int $navigationSort = 7;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Queued')->dateTime()->sortable(),
                TextColumn::make('type')->label('Type')->state(fn (EmailLog $record) => $record->typeLabel())->badge(),
                TextColumn::make('subject')->limit(50)->searchable(),
                TextColumn::make('to')->label('Recipient(s)')->state(fn (EmailLog $record) => $record->recipients())->limit(40)->searchable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('error')->label('Error')->limit(60)->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sent_at')->dateTime()->sortable()->toggleable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')->options(collect(EmailLogStatus::cases())->mapWithKeys(fn (EmailLogStatus $status) => [$status->value => $status->label()])),
            ])
            ->emptyStateHeading('No emails sent yet')
            ->emptyStateDescription('Every mailable and notification sent by the app will show up here.')
            ->recordActions([
                Action::make('view')
                    ->label('View')
                    ->icon(Heroicon::OutlinedEye)
                    ->schema([
                        Textarea::make('error')->label('Error')->rows(10)->disabled()->columnSpanFull(),
                    ])
                    ->fillForm(fn (EmailLog $record) => ['error' => $record->error ?: 'No error recorded.'])
                    ->modalSubmitAction(false)
                    ->modalCancelActionLabel('Close')
                    ->visible(fn (EmailLog $record) => $record->status === EmailLogStatus::Failed),
                Action::make('resend')
                    ->label('Resend')
                    ->icon(Heroicon::OutlinedArrowPath)
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (EmailLog $record) => $record->status === EmailLogStatus::Failed && $record->failed_job_uuid)
                    ->action(function (EmailLog $record) {
                        Artisan::call('queue:retry', ['id' => [$record->failed_job_uuid]]);

                        Notification::make()->title('Email re-queued for sending')->success()->send();
                    }),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    BulkAction::make('resend')
                        ->label('Resend selected')
                        ->icon(Heroicon::OutlinedArrowPath)
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function ($records) {
                            $uuids = $records->filter(fn (EmailLog $record) => $record->status === EmailLogStatus::Failed && $record->failed_job_uuid)
                                ->pluck('failed_job_uuid')
                                ->all();

                            if ($uuids !== []) {
                                Artisan::call('queue:retry', ['id' => $uuids]);
                            }

                            Notification::make()->title('Selected emails re-queued for sending')->success()->send();
                        }),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageEmailLogs::route('/'),
        ];
    }
}
