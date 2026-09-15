<?php

namespace App\Filament\Resources\MemberDocuments\Pages;

use App\Filament\Resources\MemberDocuments\MemberDocumentResource;
use App\Notifications\NewMemberDocument;
use Filament\Resources\Pages\CreateRecord;

class CreateMemberDocument extends CreateRecord
{
    protected static string $resource = MemberDocumentResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['uploaded_by'] = auth()->id();

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->user->notify(new NewMemberDocument($this->record));
    }
}
