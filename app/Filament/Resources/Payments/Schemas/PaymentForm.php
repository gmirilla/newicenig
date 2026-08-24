<?php

namespace App\Filament\Resources\Payments\Schemas;

use App\Enums\PaymentStatus;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PaymentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->components([
                        TextInput::make('reference')->required()->disabled(),
                        TextInput::make('gateway_reference')->disabled(),
                        TextInput::make('amount')->numeric()->disabled(),
                        TextInput::make('currency')->disabled(),
                        Select::make('status')
                            ->options(collect(PaymentStatus::cases())->mapWithKeys(fn ($case) => [$case->value => $case->label()]))
                            ->required(),
                        TextInput::make('channel')->disabled(),
                    ]),
            ]);
    }
}
