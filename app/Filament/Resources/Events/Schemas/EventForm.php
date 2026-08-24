<?php

namespace App\Filament\Resources\Events\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->components([
                        TextInput::make('title')
                            ->required()
                            ->live(onBlur: true)
                            ->maxLength(255)
                            ->columnSpan(2),
                        TextInput::make('slug')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->columnSpan(2),
                        DateTimePicker::make('starts_at')->required(),
                        DateTimePicker::make('ends_at'),
                        TextInput::make('location')->maxLength(255),
                        TextInput::make('cpd_points')->numeric(),
                        TextInput::make('registration_url')->url()->maxLength(255),
                        Toggle::make('is_virtual')->default(false),
                        Toggle::make('is_published')->default(false),
                    ]),
                Section::make('Cover image')
                    ->components([
                        SpatieMediaLibraryFileUpload::make('cover')
                            ->collection('cover')
                            ->image()
                            ->columnSpanFull(),
                    ]),
                Section::make('Description')
                    ->components([
                        RichEditor::make('description')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
