<?php

namespace App\Filament\Resources\NetworkConnections\Pages;

use App\Domain\Access\Actions\TestNetworkConnection;
use App\Filament\Resources\NetworkConnections\NetworkConnectionResource;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditNetworkConnection extends EditRecord
{
    protected static string $resource = NetworkConnectionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('test')
                ->label('Tester la connexion')
                ->action(function (): void {
                    $success = app(TestNetworkConnection::class)->execute($this->record);

                    Notification::make()
                        ->title($success ? 'Connexion opérationnelle' : 'Échec de connexion')
                        ->color($success ? 'success' : 'danger')
                        ->send();
                }),
        ];
    }
}
