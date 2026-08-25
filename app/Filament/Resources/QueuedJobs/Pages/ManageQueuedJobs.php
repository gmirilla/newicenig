<?php

namespace App\Filament\Resources\QueuedJobs\Pages;

use App\Filament\Resources\QueuedJobs\QueuedJobResource;
use Filament\Resources\Pages\ManageRecords;

class ManageQueuedJobs extends ManageRecords
{
    protected static string $resource = QueuedJobResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
