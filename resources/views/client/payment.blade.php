<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Paiement — Mirev Access</title>
    <style>
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; background: #07131f; color: #f4fbff; font-family: Inter, ui-sans-serif, system-ui, sans-serif; }
        main { width: min(520px, calc(100% - 48px)); padding: 40px; background: #102433; border: 1px solid #234459; border-radius: 24px; }
        .status { color: #66e3c4; font-weight: 800; letter-spacing: .08em; }
        p { color: #aac1cf; }
        button { width: 100%; padding: 15px; margin-top: 20px; border: 0; border-radius: 12px; background: #66e3c4; color: #052019; font-weight: 800; cursor: pointer; }
        small { display: block; margin-top: 16px; color: #7893a3; }
    </style>
</head>
<body>
<main>
    <div class="status">PAIEMENT EN ATTENTE</div>
    <h1>Confirmez sur votre téléphone</h1>
    <p>Forfait : <strong>{{ $attempt->order->plan->name }}</strong></p>
    <p>Montant : <strong>{{ number_format($attempt->order->amount_minor, 0, ',', ' ') }} {{ $attempt->order->currency }}</strong></p>
    <p>Numéro : {{ $attempt->order->customer->phone }}</p>
    <p>Référence : {{ $attempt->external_reference }}</p>

    @if (app()->environment(['local', 'testing']))
        <form method="post" action="{{ route('payments.simulate-success', $attempt) }}">
            @csrf
            <button type="submit">Simuler la confirmation Mobile Money</button>
        </form>
        <small>Ce bouton existe uniquement en local. En production, le fournisseur confirmera le paiement par webhook.</small>
    @endif
</main>
</body>
</html>
