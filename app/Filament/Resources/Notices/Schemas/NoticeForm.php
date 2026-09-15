<?php

namespace App\Filament\Resources\Notices\Schemas;

use App\Models\MembershipTier;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class NoticeForm
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
                            ->maxLength(255)
                            ->columnSpanFull(),
                        Toggle::make('is_pinned')
                            ->label('Pin to top of the board'),
                        Toggle::make('is_published')
                            ->label('Published')
                            ->helperText('Turning this on notifies eligible members immediately (unless a future publish date below hasn\'t arrived yet).'),
                        DateTimePicker::make('published_at')
                            ->label('Publish date')
                            ->helperText('Leave blank to publish immediately when turned on.'),
                        DateTimePicker::make('expires_at')
                            ->label('Hide from the board after'),
                        Select::make('membershipTiers')
                            ->label('Restrict to tiers')
                            ->relationship('membershipTiers', 'name')
                            ->options(fn () => MembershipTier::pluck('name', 'id'))
                            ->multiple()
                            ->helperText('Leave empty to show this notice to all members.')
                            ->columnSpanFull(),
                    ]),
                Section::make('Content')
                    ->components([
                        RichEditor::make('body')
                            ->required()
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
