<?php

namespace App\Filament\Resources\UserMemberships\Pages;

use App\Filament\Resources\UserMemberships\UserMembershipResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUserMembership extends CreateRecord
{
    protected static string $resource = UserMembershipResource::class;
}
