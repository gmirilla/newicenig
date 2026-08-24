<?php

namespace App\Filament\Resources\Testimonials\Schemas;

use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TestimonialForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->components([
                        TextInput::make('author_name')->required()->maxLength(255),
                        TextInput::make('author_role')->maxLength(255),
                        TextInput::make('display_order')->numeric()->default(0),
                        Toggle::make('is_featured')->default(false),
                        Textarea::make('quote')->required()->rows(4)->columnSpanFull(),
                    ]),
                Section::make('Photo')
                    ->components([
                        SpatieMediaLibraryFileUpload::make('photo')
                            ->collection('photo')
                            ->image()
                            ->avatar()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
