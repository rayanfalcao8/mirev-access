<?php

namespace App\Http\Controllers;

use App\Domain\Payment\Actions\StartCheckout;
use App\Models\Plan;
use App\Models\Site;
use App\Models\Subscription;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClientPortalController extends Controller
{
    public function show(Site $site): View
    {
        return view('client.portal', [
            'site' => $site,
            'plans' => $site->plans()->where('is_active', true)->get(),
        ]);
    }

    public function purchase(
        Request $request,
        Site $site,
        StartCheckout $checkout,
    ): RedirectResponse {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
            'plan_id' => ['required', 'integer'],
        ]);

        $plan = Plan::query()
            ->whereBelongsTo($site)
            ->where('is_active', true)
            ->findOrFail($validated['plan_id']);

        $attempt = $checkout->execute(
            $site,
            $plan,
            $validated['phone'],
            (string) config('services.payments.default'),
        );

        return redirect()->route('payments.show', $attempt);
    }

    public function success(Site $site, Subscription $subscription): View
    {
        abort_unless($subscription->plan()->whereBelongsTo($site)->exists(), 404);

        return view('client.success', [
            'site' => $site,
            'subscription' => $subscription->load(['plan', 'accessGrant']),
        ]);
    }
}
