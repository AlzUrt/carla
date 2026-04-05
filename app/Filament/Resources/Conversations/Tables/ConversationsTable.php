<?php

namespace App\Filament\Resources\Conversations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ViewColumn;
use Filament\Tables\Table;

class ConversationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                ViewColumn::make('audio_question')
                    ->label('Ma voix')
                    ->view('filament.columns.audio-column')
                    ->viewData(['type' => 'question']),
                
                TextColumn::make('text_question')
                    ->label('Question transcrite')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                
                TextColumn::make('text_answer')
                    ->label('Réponse')
                    ->searchable()
                    ->sortable()
                    ->limit(50),
                
                ViewColumn::make('audio_answer')
                    ->label('Voix IA')
                    ->view('filament.columns.audio-column')
                    ->viewData(['type' => 'answer']),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
