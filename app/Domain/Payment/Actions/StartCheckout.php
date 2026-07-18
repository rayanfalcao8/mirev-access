<?php

namespace App\Domain\Payment\Actions;

use App\Domain\Payment\Services\PaymentProviderRegistry;
use App\Models\Customer;
use App\Models\Order;
use App\Models\PaymentAttempt;
use App\Models\Plan;
use App\Models\Site;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StartCheckout
{
    public function __construct(
        private readonly PaymentProviderRegistry $providers,
    ) {}

    public function execute(Site $site, Plan $plan, string $phone, string $provider = 'fake'): PaymentAttempt
    {
        return DB::transaction(function () use ($site, $plan, $phone, $provider) {
            $customer = Customer::query()->firstOrCreate(['phone' => $phone]);
            $checkoutReference = 'checkout_'.Str::uuid();

            $order = Order::query()->create([
                'site_id' => $site->id,
                'plan_id' => $plan->id,
                'customer_id' => $customer->id,
                'status' => 'pending',
                'amount_minor' => $plan->price_minor,
                'currency' => $site->currency,
                'payment_reference' => $checkoutReference,
            ]);

            $attempt = PaymentAttempt::query()->create([
                'public_id' => Str::uuid(),
                'order_id' => $order->id,
                'provider' => $provider,
                'method' => 'mobile_money',
                'status' => 'pending',
                'idempotency_key' => $checkoutReference,
            ]);

            $attempt->update([
                'external_reference' => $this->providers->for($provider)->initiate($attempt),
            ]);

            return $attempt->fresh(['order.plan', 'order.site', 'order.customer']);
        });
    }
}
