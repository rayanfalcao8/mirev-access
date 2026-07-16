<?php

namespace App\Filament\Resources\Subscriptions\Pages;

use App\Domain\Subscription\Actions\ManageSubscription;
use App\Filament\Resources\Subscriptions\SubscriptionResource;
use App\Models\Subscription;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use RuntimeException;

class ViewSubscription extends ViewRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return SubscriptionResource::managementActions();
    }
}
