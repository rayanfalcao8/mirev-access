<?php

namespace App\Filament\Resources\Incidents;

use App\Filament\Resources\Incidents\Pages\ListIncidents;
use App\Models\Incident;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class IncidentResource extends Resource
{
    protected static ?string $model = Incident::class;
    protected static ?string $modelLabel = 'incident';
    protected static ?string $pluralModelLabel = 'incidents';
    protected static ?int $navigationSort = 40;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('severity')->label('Sévérité')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'critical', 'error' => 'danger',
                        'warning' => 'warning',
                        default => 'info',
                    }),
                TextColumn::make('title')->label('Incident')->searchable()->wrap(),
                TextColumn::make('type')->label('Type')->searchable(),
                TextColumn::make('status')->label('État')->badge()
                    ->color(fn (string $state): string => $state === 'open' ? 'danger' : 'success'),
                TextColumn::make('updated_at')->label('Dernier signal')->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('État')->options(['open' => 'Ouvert', 'resolved' => 'Résolu']),
                SelectFilter::make('severity')->label('Sévérité')->options([
                    'info' => 'Info',
                    'warning' => 'Avertissement',
                    'error' => 'Erreur',
                    'critical' => 'Critique',
                ]),
            ])
            ->recordActions([
                Action::make('resolve')
                    ->label('Résoudre')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (Incident $record): bool => $record->status === 'open')
                    ->action(function (Incident $record): void {
                        $record->update(['status' => 'resolved', 'resolved_at' => now()]);
                        Notification::make()->title('Incident résolu')->success()->send();
                    }),
                Action::make('reopen')
                    ->label('Rouvrir')
                    ->visible(fn (Incident $record): bool => $record->status === 'resolved')
                    ->action(fn (Incident $record) => $record->update(['status' => 'open', 'resolved_at' => null])),
            ])
            ->defaultSort('updated_at', 'desc');
    }

    public static function getPages(): array
    {
        return ['index' => ListIncidents::route('/')];
    }
}
