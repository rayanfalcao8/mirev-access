<?php

namespace App\Filament\Widgets;

use App\Models\AccessGrant;
use App\Models\Incident;
use App\Models\Order;
use App\Models\Subscription;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class OperationsOverview extends StatsOverviewWidget
{
    protected ?string $pollingInterval = '30s';

    protected function getStats(): array
    {
        $revenue = Order::query()->where('status', 'paid')->sum('amount_minor');

        return [
            Stat::make('Abonnements actifs', Subscription::query()->where('status', 'active')->count())
                ->description('Accès actuellement valides')
                ->color('success'),
            Stat::make('Revenu encaissé', number_format($revenue, 0, ',', ' ').' XAF')
                ->description('Paiements confirmés')
                ->color('primary'),
            Stat::make('Accès réseau', AccessGrant::query()->where('status', 'authorized')->count())
                ->description('Autorisations actives')
                ->color('info'),
            Stat::make('Incidents ouverts', Incident::query()->where('status', 'open')->count())
                ->description('À surveiller')
                ->color(Incident::query()->where('status', 'open')->exists() ? 'danger' : 'success'),
        ];
    }
}
