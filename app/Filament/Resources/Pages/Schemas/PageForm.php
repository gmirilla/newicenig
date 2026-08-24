<?php

namespace App\Filament\Resources\Pages\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PageForm
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
                        Textarea::make('meta_description')
                            ->maxLength(255)
                            ->rows(2)
                            ->columnSpan(2),
                        Toggle::make('is_published')
                            ->default(false),
                    ]),
                Section::make('Content')
                    ->components([
                        RichEditor::make('body')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
