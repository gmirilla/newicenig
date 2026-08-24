<?php

namespace App\Filament\Resources\UserMemberships\Pages;

use App\Filament\Resources\UserMemberships\UserMembershipResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListUserMemberships extends ListRecords
{
    protected static string $resource = UserMembershipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
