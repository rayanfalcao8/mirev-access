<?php

namespace App\Filament\Resources\Orders;

use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Models\Order;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;
    protected static ?string $modelLabel = 'commande';
    protected static ?string $pluralModelLabel = 'commandes';
    protected static ?int $navigationSort = 35;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('payment_reference')->label('Référence')->searchable()->copyable(),
                TextColumn::make('customer.phone')->label('Client')->searchable(),
                TextColumn::make('site.name')->label('Site')->sortable(),
                TextColumn::make('plan.name')->label('Forfait')->sortable(),
                TextColumn::make('amount_minor')->label('Montant')
                    ->formatStateUsing(fn (int $state, Order $record): string => number_format($state, 0, ',', ' ').' '.$record->currency)
                    ->sortable(),
                TextColumn::make('status')->label('Paiement')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'paid' => 'success',
                        'failed', 'refunded' => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('paid_at')->label('Payé le')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->label('État')->options([
                    'pending' => 'En attente',
                    'paid' => 'Payé',
                    'failed' => 'Échoué',
                    'refunded' => 'Remboursé',
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return ['index' => ListOrders::route('/')];
    }
}
