<?php

namespace App\Filament\Resources\BankAccounts\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BankAccountForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->components([
                        TextInput::make('bank_name')->required()->maxLength(255),
                        TextInput::make('account_name')->required()->maxLength(255),
                        TextInput::make('account_number')->required()->maxLength(255),
                        TextInput::make('currency')->required()->default('NGN')->maxLength(3),
                        Toggle::make('is_active')->default(true),
                        TextInput::make('sort_order')->numeric()->default(0),
                        Textarea::make('instructions')
                            ->label('Notes shown to members')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
