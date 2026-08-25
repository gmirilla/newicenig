<?php

namespace App\Filament\Pages;

use App\Models\FailedJob;
use App\Models\QueuedJob;
use App\Services\Queue\QueueWorkerManager;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class QueueMonitor extends Page
{
    protected string $view = 'filament.pages.queue-monitor';

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedServerStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    protected static ?string $navigationLabel = 'Queue Monitor';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'Queue Monitor';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super-admin') ?? false;
    }

    public function getPendingCount(): int
    {
        return QueuedJob::count();
    }

    public function getFailedCount(): int
    {
        return FailedJob::count();
    }

    public function getWorkerStatus(): array
    {
        return app(QueueWorkerManager::class)->status();
    }

    protected function getHeaderActions(): array
    {
        $manager = app(QueueWorkerManager::class);
        $running = $manager->status()['running'];

        return [
            Action::make('start')
                ->label('Start worker')
                ->icon(Heroicon::OutlinedPlay)
                ->color('success')
                ->visible(! $running)
                ->action(function () use ($manager) {
                    $started = $manager->start();

                    $notification = Notification::make()
                        ->title($started ? 'Worker started' : 'Could not start the worker')
                        ->body($started ? null : 'Check the server logs — the worker process failed to launch.');

                    $started ? $notification->success() : $notification->danger();

                    $notification->send();
                }),

            Action::make('stop')
                ->label('Stop worker')
                ->icon(Heroicon::OutlinedStop)
                ->color('danger')
                ->requiresConfirmation()
                ->modalDescription('The worker will stop immediately — any job it is currently processing will be interrupted and retried later.')
                ->visible($running)
                ->action(function () use ($manager) {
                    $stopped = $manager->stop();

                    $notification = Notification::make()
                        ->title($stopped ? 'Worker stopped' : 'No worker was running');

                    $stopped ? $notification->success() : $notification->warning();

                    $notification->send();
                }),

            Action::make('restart')
                ->label('Restart workers')
                ->icon(Heroicon::OutlinedArrowPath)
                ->color('gray')
                ->requiresConfirmation()
                ->modalDescription('Signals every running worker to finish its current job and restart. Safe to run at any time.')
                ->action(function () use ($manager) {
                    $manager->restart();

                    Notification::make()->title('Restart signal sent')->success()->send();
                }),
        ];
    }
}
