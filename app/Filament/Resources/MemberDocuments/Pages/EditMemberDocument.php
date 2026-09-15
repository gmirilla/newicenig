<?php

namespace App\Filament\Resources\MemberDocuments\Pages;

use App\Filament\Resources\MemberDocuments\MemberDocumentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMemberDocument extends EditRecord
{
    protected static string $resource = MemberDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
