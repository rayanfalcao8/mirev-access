<?php

namespace App\Filament\Resources\NetworkConnections\Pages;

use App\Filament\Resources\NetworkConnections\NetworkConnectionResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNetworkConnections extends ListRecords
{
    protected static string $resource = NetworkConnectionResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Configurer un site')];
    }
}
