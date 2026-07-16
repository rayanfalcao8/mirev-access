<?php

namespace App\Filament\Resources\Subscriptions\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TransitionsRelationManager extends RelationManager
{
    protected static string $relationship = 'transitions';
    protected static ?string $title = 'Historique';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('created_at')->label('Date')->dateTime('d/m/Y H:i:s')->sortable(),
                TextColumn::make('from_status')->label('État précédent')->badge()->placeholder('Création'),
                TextColumn::make('to_status')->label('Nouvel état')->badge(),
                TextColumn::make('reason')->label('Motif')->searchable()->wrap(),
                TextColumn::make('metadata.actor_id')->label('Administrateur')->prefix('#')->placeholder('Système'),
                TextColumn::make('metadata.minutes')->label('Minutes ajoutées')->placeholder('—'),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
