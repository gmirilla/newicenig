<?php

namespace App\Filament\Resources\MemberDocuments;

use App\Filament\Resources\MemberDocuments\Pages\CreateMemberDocument;
use App\Filament\Resources\MemberDocuments\Pages\EditMemberDocument;
use App\Filament\Resources\MemberDocuments\Pages\ListMemberDocuments;
use App\Filament\Resources\MemberDocuments\Schemas\MemberDocumentForm;
use App\Filament\Resources\MemberDocuments\Tables\MemberDocumentsTable;
use App\Models\MemberDocument;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MemberDocumentResource extends Resource
{
    protected static ?string $model = MemberDocument::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static ?string $navigationLabel = 'Member Documents';

    protected static string|\UnitEnum|null $navigationGroup = 'Membership';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return MemberDocumentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MemberDocumentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMemberDocuments::route('/'),
            'create' => CreateMemberDocument::route('/create'),
            'edit' => EditMemberDocument::route('/{record}/edit'),
        ];
    }
}
