<?php

namespace App\Http\Controllers;

use App\Domain\Subscription\Actions\ActivateSimulatedPurchase;
use App\Models\Plan;
use App\Models\Site;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
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
        ActivateSimulatedPurchase $purchase,
    ): RedirectResponse {
        $validated = $request->validate([
            'phone' => ['required', 'string', 'max:30'],
            'plan_id' => ['required', 'integer'],
        ]);

        $plan = Plan::query()
            ->whereBelongsTo($site)
            ->where('is_active', true)
            ->findOrFail($validated['plan_id']);

        $subscription = $purchase->execute(
            $site,
            $plan,
            $validated['phone'],
            'sim_'.Str::uuid(),
        );

        return redirect()
            ->route('client.success', [$site, $subscription])
            ->with('status', 'Votre accès Internet est activé.');
    }
}
