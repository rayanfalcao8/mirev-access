<?php

namespace App\Filament\Resources\Subscriptions;

use App\Domain\Subscription\Actions\ManageSubscription;
use App\Filament\Resources\Subscriptions\Pages\ListSubscriptions;
use App\Filament\Resources\Subscriptions\Pages\ViewSubscription;
use App\Filament\Resources\Subscriptions\RelationManagers\TransitionsRelationManager;
use App\Models\Subscription;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use RuntimeException;

class SubscriptionResource extends Resource
{
    protected static ?string $model = Subscription::class;
    protected static ?string $modelLabel = 'abonnement';
    protected static ?string $pluralModelLabel = 'abonnements';
    protected static ?int $navigationSort = 30;

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Abonnement')->schema([
                TextEntry::make('id')->label('Identifiant')->prefix('#'),
                TextEntry::make('status')->label('État')->badge(),
                TextEntry::make('customer.phone')->label('Client')->copyable(),
                TextEntry::make('plan.name')->label('Forfait'),
                TextEntry::make('starts_at')->label('Début')->dateTime('d/m/Y H:i'),
                TextEntry::make('expires_at')->label('Expiration')->dateTime('d/m/Y H:i'),
            ])->columns(3),
            Section::make('Accès réseau')->schema([
                TextEntry::make('accessGrant.provider')->label('Fournisseur')->placeholder('Non attribué'),
                TextEntry::make('accessGrant.status')->label('État')->badge()->placeholder('Non attribué'),
                TextEntry::make('accessGrant.external_reference')->label('Référence externe')->copyable()->placeholder('—'),
                TextEntry::make('accessGrant.authorized_at')->label('Autorisé le')->dateTime('d/m/Y H:i')->placeholder('—'),
                TextEntry::make('accessGrant.revoked_at')->label('Révoqué le')->dateTime('d/m/Y H:i')->placeholder('—'),
                TextEntry::make('accessGrant.last_error')->label('Dernière erreur')->placeholder('Aucune')->columnSpanFull(),
            ])->columns(3),
            Section::make('Paiement')->schema([
                TextEntry::make('order.payment_reference')->label('Référence')->copyable(),
                TextEntry::make('order.amount_minor')->label('Montant')
                    ->formatStateUsing(fn (int $state, Subscription $record): string => number_format($state, 0, ',', ' ').' '.$record->order->currency),
                TextEntry::make('order.paid_at')->label('Payé le')->dateTime('d/m/Y H:i'),
            ])->columns(3),
        ]);
    }

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
            ->recordActions([
                ViewAction::make(),
                ...self::managementActions(),
            ])
            ->defaultSort('expires_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [TransitionsRelationManager::class];
    }

    public static function managementActions(): array
    {
        return [
            Action::make('suspend')
                ->label('Suspendre')
                ->color('warning')
                ->requiresConfirmation()
                ->visible(fn (Subscription $record): bool => in_array($record->status, ['active', 'expiring'], true))
                ->action(fn (Subscription $record) => self::runAction(
                    fn () => app(ManageSubscription::class)->suspend($record, auth()->id()),
                    'Abonnement suspendu',
                )),
            Action::make('reactivate')
                ->label('Réactiver')
                ->color('success')
                ->requiresConfirmation()
                ->visible(fn (Subscription $record): bool => $record->status === 'suspended')
                ->action(fn (Subscription $record) => self::runAction(
                    fn () => app(ManageSubscription::class)->reactivate($record, auth()->id()),
                    'Abonnement réactivé',
                )),
            Action::make('extend')
                ->label('Prolonger')
                ->color('info')
                ->form([
                    TextInput::make('minutes')
                        ->label('Minutes à ajouter')
                        ->numeric()
                        ->minValue(1)
                        ->default(1440)
                        ->required(),
                ])
                ->action(fn (Subscription $record, array $data) => self::runAction(
                    fn () => app(ManageSubscription::class)->extend($record, (int) $data['minutes'], auth()->id()),
                    'Abonnement prolongé',
                )),
            Action::make('expire')
                ->label('Expirer')
                ->color('danger')
                ->requiresConfirmation()
                ->visible(fn (Subscription $record): bool => $record->status !== 'expired')
                ->action(fn (Subscription $record) => self::runAction(
                    fn () => app(ManageSubscription::class)->expire($record, auth()->id()),
                    'Abonnement expiré',
                )),
            Action::make('reconcile')
                ->label('Réconcilier')
                ->requiresConfirmation()
                ->action(fn (Subscription $record) => self::runAction(
                    fn () => app(ManageSubscription::class)->reconcile($record, auth()->id()),
                    'État réseau réconcilié',
                )),
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubscriptions::route('/'),
            'view' => ViewSubscription::route('/{record}'),
        ];
    }

    private static function runAction(callable $callback, string $successMessage): void
    {
        try {
            $callback();
            Notification::make()->title($successMessage)->success()->send();
        } catch (RuntimeException $exception) {
            Notification::make()->title('Action impossible')->body($exception->getMessage())->danger()->send();
        }
    }
}
