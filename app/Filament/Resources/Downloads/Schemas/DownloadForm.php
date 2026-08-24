<?php

namespace App\Filament\Resources\Downloads\Schemas;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DownloadForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->components([
                        TextInput::make('title')->required()->maxLength(255),
                        TextInput::make('category')->required()->default('General')->maxLength(255),
                        Toggle::make('is_public')->default(true),
                        Textarea::make('description')->rows(3)->columnSpanFull(),
                    ]),
                Section::make('File')
                    ->components([
                        SpatieMediaLibraryFileUpload::make('file')
                            ->collection('file')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
