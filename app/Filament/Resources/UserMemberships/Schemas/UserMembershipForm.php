<?php

namespace App\Filament\Resources\UserMemberships\Schemas;

use App\Enums\MembershipStatus;
use App\Models\MembershipTier;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class UserMembershipForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Membership')
                    ->columns(2)
                    ->components([
                        Select::make('membership_tier_id')
                            ->label('Tier')
                            ->options(MembershipTier::pluck('name', 'id'))
                            ->required()
                            ->searchable(),
                        Select::make('status')
                            ->options(collect(MembershipStatus::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()]))
                            ->required(),
                        TextInput::make('membership_number')->maxLength(255),
                        DateTimePicker::make('expires_at'),
                        Toggle::make('directory_opt_in'),
                    ]),
                Section::make('Applicant details')
                    ->columns(2)
                    ->components([
                        TextInput::make('first_name')->required(),
                        TextInput::make('last_name')->required(),
                        TextInput::make('email')->email()->required(),
                        TextInput::make('phone'),
                        TextInput::make('employer_name'),
                        TextInput::make('job_title'),
                        TextInput::make('years_of_experience')->numeric(),
                        TextInput::make('state_of_origin'),
                        TextInput::make('qualification'),
                        DatePicker::make('date_of_birth'),
                    ]),
            ]);
    }
}
