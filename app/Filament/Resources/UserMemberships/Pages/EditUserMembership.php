<?php

namespace App\Filament\Resources\UserMemberships\Pages;

use App\Filament\Resources\UserMemberships\UserMembershipResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUserMembership extends EditRecord
{
    protected static string $resource = UserMembershipResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
