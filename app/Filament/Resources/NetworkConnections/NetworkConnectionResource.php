<?php

namespace App\Filament\Resources\NetworkConnections;

use App\Domain\Access\Actions\TestNetworkConnection;
use App\Filament\Resources\NetworkConnections\Pages\CreateNetworkConnection;
use App\Filament\Resources\NetworkConnections\Pages\EditNetworkConnection;
use App\Filament\Resources\NetworkConnections\Pages\ListNetworkConnections;
use App\Models\NetworkConnection;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NetworkConnectionResource extends Resource
{
    protected static ?string $model = NetworkConnection::class;
    protected static ?string $modelLabel = 'connexion réseau';
    protected static ?string $pluralModelLabel = 'connexions réseau';
    protected static ?int $navigationSort = 15;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Site et connecteur')->schema([
                Select::make('site_id')->label('Site')->relationship('site', 'name')->required()->searchable()->preload()->unique(ignoreRecord: true),
                Select::make('provider')->label('Connecteur')->options([
                    'fake' => 'Simulateur Mirev',
                ])->default('fake')->required(),
            ])->columns(2),
            Section::make('Configuration du connecteur')->description('Ces valeurs sont chiffrées dans la base de données.')->schema([
                TextInput::make('configuration.endpoint')->label('Adresse du contrôleur')->url()->placeholder('https://controller.example.com'),
                TextInput::make('configuration.username')->label('Identifiant'),
                TextInput::make('configuration.secret')->label('Secret / mot de passe')->password()->revealable(),
                TextInput::make('configuration.site_reference')->label('Référence du site chez le fournisseur'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('site.name')->label('Site')->searchable()->sortable(),
                TextColumn::make('provider')->label('Connecteur')->badge(),
                TextColumn::make('status')->label('Santé')->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'connected' => 'success',
                        'failed' => 'danger',
                        default => 'warning',
                    }),
                TextColumn::make('last_tested_at')->label('Dernier test')->since()->placeholder('Jamais'),
                TextColumn::make('last_error')->label('Dernière erreur')->limit(50)->placeholder('Aucune'),
            ])
            ->recordActions([
                Action::make('test')
                    ->label('Tester')
                    ->action(function (NetworkConnection $record): void {
                        $success = app(TestNetworkConnection::class)->execute($record);

                        Notification::make()
                            ->title($success ? 'Connexion opérationnelle' : 'Échec de connexion')
                            ->color($success ? 'success' : 'danger')
                            ->send();
                    }),
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNetworkConnections::route('/'),
            'create' => CreateNetworkConnection::route('/create'),
            'edit' => EditNetworkConnection::route('/{record}/edit'),
        ];
    }
}
