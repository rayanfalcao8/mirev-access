<?php

namespace App\Filament\Resources\Subscriptions;

use App\Filament\Resources\Subscriptions\Pages\ListSubscriptions;
use App\Models\Subscription;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;
    protected static ?string $modelLabel = 'abonnement';
    protected static ?string $pluralModelLabel = 'abonnements';
    protected static ?int $navigationSort = 30;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')->label('#')->sortable(),
                TextColumn::make('customer.phone')->label('Client')->searchable(),
                TextColumn::make('plan.name')->label('Forfait')->searchable(),
                TextColumn::make('status')->label('État')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'expired' => 'gray',
                        'suspended' => 'warning',
                        default => 'info',
                    }),
                TextColumn::make('accessGrant.status')->label('Réseau')->badge(),
                TextColumn::make('starts_at')->label('Début')->dateTime('d/m/Y H:i')->sortable(),
                TextColumn::make('expires_at')->label('Expiration')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('État')->options([
                    'active' => 'Actif',
                    'expired' => 'Expiré',
                    'suspended' => 'Suspendu',
                ]),
            ])
            ->defaultSort('expires_at', 'desc');
    }

    public static function getPages(): array
    {
        return ['index' => ListSubscriptions::route('/')];
    }
}
