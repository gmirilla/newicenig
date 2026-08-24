<?php

namespace App\Filament\Resources\TeamMembers\Schemas;

use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class TeamMemberForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->components([
                        TextInput::make('name')->required()->maxLength(255),
                        TextInput::make('role_title')->required()->maxLength(255),
                        TextInput::make('linkedin_url')->url()->maxLength(255),
                        TextInput::make('display_order')->numeric()->default(0),
                        Toggle::make('is_current')->default(true),
                    ]),
                Section::make('Photo')
                    ->components([
                        SpatieMediaLibraryFileUpload::make('photo')
                            ->collection('photo')
                            ->image()
                            ->avatar()
                            ->columnSpanFull(),
                    ]),
                Section::make('Bio')
                    ->components([
                        RichEditor::make('bio')->columnSpanFull(),
                    ]),
            ]);
    }
}
