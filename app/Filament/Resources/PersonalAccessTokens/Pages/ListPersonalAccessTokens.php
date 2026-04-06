<?php

namespace App\Filament\Resources\PersonalAccessTokens\Pages;

use App\Filament\Resources\PersonalAccessTokens\PersonalAccessTokenResource;
use Filament\Resources\Pages\ListRecords;

class ListPersonalAccessTokens extends ListRecords
{
    protected static string $resource = PersonalAccessTokenResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
