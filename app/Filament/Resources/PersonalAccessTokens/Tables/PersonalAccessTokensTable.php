<?php

namespace App\Filament\Resources\PersonalAccessTokens\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PersonalAccessTokensTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('name')
                    ->label('Nom')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('tokenable_type')
                    ->label('Type')
                    ->formatStateUsing(fn (?string $state): string => class_basename((string) $state))
                    ->badge(),

                TextColumn::make('tokenable.name')
                    ->label('Utilisateur')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('abilities')
                    ->label('Permissions')
                    ->formatStateUsing(fn ($state): string => is_array($state) ? implode(', ', $state) : (string) $state)
                    ->toggleable(),

                TextColumn::make('last_used_at')
                    ->label('Dernière utilisation')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('Jamais'),

                TextColumn::make('expires_at')
                    ->label('Expire le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->placeholder('Jamais'),

                TextColumn::make('created_at')
                    ->label('Créé le')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->label('Révoquer'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->label('Révoquer la sélection'),
                ]),
            ]);
    }
}
