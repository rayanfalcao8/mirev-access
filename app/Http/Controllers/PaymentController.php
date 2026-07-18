<?php

namespace App\Http\Controllers;

use App\Domain\Payment\Actions\ConfirmPayment;
use App\Models\PaymentAttempt;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class PaymentController extends Controller
{
    public function show(PaymentAttempt $attempt): View|RedirectResponse
    {
        $attempt->load(['order.site', 'order.plan', 'order.customer', 'order.subscription']);

        if ($attempt->order->subscription) {
            return redirect()->route('client.success', [
                $attempt->order->site,
                $attempt->order->subscription,
            ]);
        }

        return view('client.payment', ['attempt' => $attempt]);
    }

    public function simulateSuccess(PaymentAttempt $attempt, ConfirmPayment $confirm): RedirectResponse
    {
        abort_unless(app()->environment(['local', 'testing']), 404);

        $subscription = $confirm->execute($attempt, ['source' => 'local_simulator']);

        return redirect()
            ->route('client.success', [$attempt->order->site, $subscription])
            ->with('status', 'Paiement confirmé. Votre accès Internet est activé.');
    }
}
