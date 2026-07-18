<?php

namespace App\Filament\Resources\Customers;

use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Models\Customer;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;
    protected static ?string $modelLabel = 'client';
    protected static ?string $pluralModelLabel = 'clients';
    protected static ?int $navigationSort = 25;

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('phone')->label('Téléphone')->searchable()->copyable(),
                TextColumn::make('orders_count')->label('Commandes')->counts('orders')->sortable(),
                TextColumn::make('subscriptions_count')->label('Abonnements')->counts('subscriptions')->sortable(),
                TextColumn::make('created_at')->label('Premier achat')->dateTime('d/m/Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return ['index' => ListCustomers::route('/')];
    }
}
