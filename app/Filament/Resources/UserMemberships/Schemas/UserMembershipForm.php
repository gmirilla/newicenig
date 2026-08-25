<?php

namespace App\Filament\Resources\UserMemberships\Schemas;

use App\Enums\MembershipStatus;
use App\Models\MembershipTier;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
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

                Section::make('Personal details')
                    ->columns(3)
                    ->components([
                        TextInput::make('first_name')->required(),
                        TextInput::make('middle_name'),
                        TextInput::make('last_name')->required(),
                        Select::make('gender')->options(['male' => 'Male', 'female' => 'Female']),
                        DatePicker::make('date_of_birth'),
                        TextInput::make('place_of_birth'),
                        TextInput::make('nationality'),
                        Select::make('marital_status')->options([
                            'single' => 'Single', 'married' => 'Married', 'divorced' => 'Divorced', 'widowed' => 'Widowed',
                        ]),
                    ]),

                Section::make('Contact & address')
                    ->columns(2)
                    ->components([
                        TextInput::make('email')->email()->required(),
                        TextInput::make('phone'),
                        TextInput::make('state_of_origin'),
                        TextInput::make('local_government_area'),
                        Textarea::make('residential_address')->columnSpanFull()->rows(2),
                        Textarea::make('postal_address')->columnSpanFull()->rows(2),
                        TextInput::make('next_of_kin_name'),
                        TextInput::make('next_of_kin_address'),
                    ]),

                Section::make('Educational qualifications')
                    ->columns(3)
                    ->components([
                        TextInput::make('primary_school')->label('Primary school'),
                        TextInput::make('primary_school_year')->label('Year passed out'),
                        TextInput::make('secondary_school')->label('Secondary school'),
                        TextInput::make('secondary_school_year')->label('Year passed out'),
                        TextInput::make('higher_institution')->label('Higher institution')->columnSpan(1),
                        TextInput::make('higher_institution_course')->label('Course of study'),
                        TextInput::make('higher_institution_year')->label('Year passed out'),
                        TextInput::make('higher_institution_grade')->label('Final grade'),
                        TextInput::make('higher_institution_second_degree')->label('Second degree'),
                    ]),

                Section::make('Professional background')
                    ->columns(2)
                    ->components([
                        TextInput::make('employer_name'),
                        TextInput::make('job_title'),
                        TextInput::make('years_of_experience')->numeric(),
                        TextInput::make('year_of_qualification'),
                        Toggle::make('belongs_to_other_institute')->columnSpanFull(),
                        TextInput::make('other_institute_name'),
                        TextInput::make('other_institute_status'),
                        TextInput::make('other_institute_membership_number'),
                        TextInput::make('qualification')->label('Qualification (legacy/summary)'),
                    ]),

                Section::make('Declaration')
                    ->columns(2)
                    ->components([
                        TextInput::make('declaration_name')->label('Signed name')->disabled(),
                        DateTimePicker::make('declaration_accepted_at')->label('Accepted at')->disabled(),
                    ]),
            ]);
    }
}
