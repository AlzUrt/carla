<?php

namespace App\Filament\Resources\PersonalAccessTokens;

use App\Filament\Resources\PersonalAccessTokens\Pages\EditPersonalAccessToken;
use App\Filament\Resources\PersonalAccessTokens\Pages\ListPersonalAccessTokens;
use App\Filament\Resources\PersonalAccessTokens\Schemas\PersonalAccessTokenForm;
use App\Filament\Resources\PersonalAccessTokens\Tables\PersonalAccessTokensTable;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Laravel\Sanctum\PersonalAccessToken;
use UnitEnum;

class PersonalAccessTokenResource extends Resource
{
    protected static ?string $model = PersonalAccessToken::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static ?string $navigationLabel = 'Tokens API';

    protected static ?string $modelLabel = 'Token API';

    protected static ?string $pluralModelLabel = 'Tokens API';

    protected static string|UnitEnum|null $navigationGroup = 'Sécurité';

    public static function form(Schema $schema): Schema
    {
        return PersonalAccessTokenForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PersonalAccessTokensTable::configure($table);
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
            'index' => ListPersonalAccessTokens::route('/'),
            'edit' => EditPersonalAccessToken::route('/{record}/edit'),
        ];
    }
}
