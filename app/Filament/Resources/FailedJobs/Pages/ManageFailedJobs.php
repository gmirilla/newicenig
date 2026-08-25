<?php

namespace App\Filament\Resources\FailedJobs\Pages;

use App\Filament\Resources\FailedJobs\FailedJobResource;
use App\Models\FailedJob;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ManageRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\Artisan;

class ManageFailedJobs extends ManageRecords
{
    protected static string $resource = FailedJobResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('retryAll')
                ->label('Retry all')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn () => FailedJob::query()->exists())
                ->action(function () {
                    Artisan::call('queue:retry', ['id' => ['all']]);

                    Notification::make()->title('All failed jobs re-queued')->success()->send();
                }),
            Action::make('clearAll')
                ->label('Clear all')
                ->icon(Heroicon::OutlinedTrash)
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn () => FailedJob::query()->exists())
                ->action(function () {
                    Artisan::call('queue:flush');

                    Notification::make()->title('Failed job list cleared')->success()->send();
                }),
        ];
    }
}
