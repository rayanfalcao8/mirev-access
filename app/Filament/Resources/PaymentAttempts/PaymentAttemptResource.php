<?php

namespace App\Filament\Resources\PaymentAttempts;

use App\Filament\Resources\PaymentAttempts\Pages\ListPaymentAttempts;
use App\Models\PaymentAttempt;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class PaymentAttemptResource extends Resource
{
    protected static ?string $model = PaymentAttempt::class;
    protected static ?string $modelLabel = 'tentative de paiement';
    protected static ?string $pluralModelLabel = 'paiements';
    protected static ?int $navigationSort = 34;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('public_id')->label('Identifiant')->copyable()->limit(12),
                TextColumn::make('order.customer.phone')->label('Client')->searchable(),
                TextColumn::make('order.plan.name')->label('Forfait'),
                TextColumn::make('order.amount_minor')->label('Montant')
                    ->formatStateUsing(fn (int $state, PaymentAttempt $record): string => number_format($state, 0, ',', ' ').' '.$record->order->currency),
                TextColumn::make('provider')->label('Fournisseur')->badge(),
                TextColumn::make('status')->label('État')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'succeeded' => 'success',
                        'failed', 'cancelled' => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('external_reference')->label('Référence fournisseur')->copyable(),
                TextColumn::make('created_at')->label('Créé')->dateTime('d/m/Y H:i:s')->sortable(),
                TextColumn::make('completed_at')->label('Confirmé')->dateTime('d/m/Y H:i:s')->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('status')->options([
                    'pending' => 'En attente',
                    'succeeded' => 'Réussi',
                    'failed' => 'Échoué',
                    'cancelled' => 'Annulé',
                ]),
                SelectFilter::make('provider')->options(['fake' => 'Simulateur']),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return ['index' => ListPaymentAttempts::route('/')];
    }
}
