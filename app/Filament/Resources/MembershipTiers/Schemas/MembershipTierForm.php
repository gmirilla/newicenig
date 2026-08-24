<?php

namespace App\Filament\Resources\MembershipTiers\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MembershipTierForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->components([
                        TextInput::make('name')
                            ->required()
                            ->live(onBlur: true)
                            ->maxLength(255),
                        TextInput::make('abbreviation')->maxLength(50),
                        TextInput::make('slug')->required()->unique(ignoreRecord: true)->maxLength(255),
                        Select::make('currency')
                            ->options(['NGN' => 'NGN', 'USD' => 'USD'])
                            ->default('NGN')
                            ->required(),
                        TextInput::make('registration_fee')->numeric()->prefix('₦')->required(),
                        TextInput::make('renewal_fee')->numeric()->prefix('₦')->required(),
                        TextInput::make('min_years_experience')->numeric(),
                        TextInput::make('sort_order')->numeric()->default(0),
                        Toggle::make('requires_employer_info')->default(false),
                        Toggle::make('requires_qualification_upload')->default(false),
                        Toggle::make('is_active')->default(true),
                        Textarea::make('description')->rows(3)->columnSpanFull(),
                    ]),
                Section::make('Benefits')
                    ->components([
                        Repeater::make('benefits')
                            ->simple(TextInput::make('benefit')->required())
                            ->addActionLabel('Add benefit')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
