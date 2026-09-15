<?php

namespace App\Filament\Resources\MemberDocuments\Pages;

use App\Filament\Resources\MemberDocuments\MemberDocumentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMemberDocuments extends ListRecords
{
    protected static string $resource = MemberDocumentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
