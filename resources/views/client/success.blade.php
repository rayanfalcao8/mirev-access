<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Accès activé — Mirev Access</title>
    <style>
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; background: #07131f; color: #f4fbff; font-family: Inter, ui-sans-serif, system-ui, sans-serif; }
        main { width: min(520px, calc(100% - 48px)); padding: 40px; background: #102433; border: 1px solid #234459; border-radius: 24px; }
        .check { color: #66e3c4; font-size: 3rem; }
        p { color: #aac1cf; }
        a { display: block; text-align: center; margin-top: 24px; padding: 14px; border-radius: 12px; background: #66e3c4; color: #052019; font-weight: 800; text-decoration: none; }
    </style>
</head>
<body>
<main>
    <div class="check">✓</div>
    <h1>Vous êtes connecté.</h1>
    <p>Forfait : <strong>{{ $subscription->plan->name }}</strong></p>
    <p>Valide jusqu’au {{ $subscription->expires_at->format('d/m/Y à H:i') }}</p>
    <p>Référence réseau : {{ $subscription->accessGrant->external_reference }}</p>
    <a href="{{ route('client.portal', $site) }}">Retour aux forfaits</a>
</main>
</body>
</html>
