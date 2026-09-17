<?php

namespace App\Filament\Resources\MembershipTiers\Schemas;

use App\Models\BankAccount;
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
                            ->options(['NGN' => 'NGN'])
                            ->default('NGN')
                            ->required()
                            ->helperText('Paystack only processes NGN for this account. Prices in other currencies (e.g. for diaspora bank transfers) are set below.'),
                        TextInput::make('registration_fee')->numeric()->prefix('₦')->required()->label('Application fee'),
                        TextInput::make('renewal_fee')->numeric()->prefix('₦')->required()->label('Annual dues'),
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
                Section::make('Additional currency prices')
                    ->description('Fixed prices for members paying via a bank account in another currency (e.g. diaspora applicants). Only currencies with a configured bank account can be selected.')
                    ->components([
                        Repeater::make('prices')
                            ->relationship('prices')
                            ->schema([
                                Select::make('currency')
                                    ->options(fn () => BankAccount::query()->pluck('currency', 'currency'))
                                    ->required()
                                    ->distinct(),
                                TextInput::make('registration_fee')->numeric()->label('Application fee'),
                                TextInput::make('renewal_fee')->numeric()->label('Annual dues'),
                            ])
                            ->columns(3)
                            ->addActionLabel('Add currency price')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
