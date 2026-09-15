<?php

namespace App\Filament\Resources\MemberDocuments\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MemberDocumentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columns(2)
                    ->components([
                        Select::make('user_id')
                            ->label('Member')
                            ->options(fn () => User::query()
                                ->orderBy('name')
                                ->get()
                                ->mapWithKeys(fn (User $user) => [$user->id => "{$user->name} ({$user->email})"]))
                            ->searchable()
                            ->required()
                            ->columnSpanFull(),
                        TextInput::make('title')
                            ->required()
                            ->maxLength(255),
                        Select::make('type')
                            ->options([
                                'certificate' => 'Certificate',
                                'resource' => 'Resource',
                                'other' => 'Other',
                            ])
                            ->default('certificate')
                            ->required(),
                        Textarea::make('description')
                            ->rows(2)
                            ->columnSpanFull(),
                    ]),
                Section::make('File')
                    ->components([
                        SpatieMediaLibraryFileUpload::make('file')
                            ->collection('file')
                            ->disk('local')
                            ->required()
                            ->maxSize(10240)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
