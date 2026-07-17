<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Mirev Access — {{ $site->name }}</title>
    <style>
        :root { color-scheme: dark; font-family: Inter, ui-sans-serif, system-ui, sans-serif; }
        body { margin: 0; min-height: 100vh; background: #07131f; color: #f4fbff; }
        main { width: min(960px, calc(100% - 32px)); margin: auto; padding: 56px 0; }
        .brand { color: #66e3c4; font-weight: 800; letter-spacing: .08em; }
        h1 { font-size: clamp(2rem, 8vw, 4.5rem); margin: 12px 0; line-height: 1; }
        .lead { color: #aac1cf; max-width: 600px; font-size: 1.1rem; }
        .plans { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin: 40px 0 24px; }
        label.plan { display: block; padding: 24px; background: #102433; border: 1px solid #234459; border-radius: 18px; cursor: pointer; }
        label.plan:has(input:checked) { border-color: #66e3c4; box-shadow: 0 0 0 2px #66e3c433; }
        .price { font-size: 1.8rem; font-weight: 800; margin-top: 16px; }
        input[type=radio] { accent-color: #66e3c4; }
        input[type=tel] { box-sizing: border-box; width: 100%; padding: 16px; border: 1px solid #345267; border-radius: 12px; background: #0d1d29; color: white; font-size: 1rem; }
        button { width: 100%; padding: 16px; margin-top: 16px; border: 0; border-radius: 12px; background: #66e3c4; color: #052019; font-weight: 800; font-size: 1rem; cursor: pointer; }
        .error { color: #ff9b9b; }
    </style>
</head>
<body>
<main>
    <div class="brand">MIREV ACCESS</div>
    <h1>Internet,<br>simplement.</h1>
    <p class="lead">Choisissez votre forfait pour {{ $site->name }}. Payez par Mobile Money pour activer votre accès. En local, la confirmation est simulée.</p>

    @if ($errors->any())
        <p class="error">{{ $errors->first() }}</p>
    @endif

    <form method="post" action="{{ route('client.purchase', $site) }}">
        @csrf
        <div class="plans">
            @forelse ($plans as $plan)
                <label class="plan">
                    <input type="radio" name="plan_id" value="{{ $plan->id }}" required>
                    <h2>{{ $plan->name }}</h2>
                    <div>{{ $plan->formatted_duration }} d’accès</div>
                    <div class="price">{{ number_format($plan->price_minor, 0, ',', ' ') }} {{ $site->currency }}</div>
                </label>
            @empty
                <p>Aucun forfait disponible actuellement.</p>
            @endforelse
        </div>

        <label for="phone">Numéro de téléphone</label>
        <input id="phone" name="phone" type="tel" value="{{ old('phone') }}" placeholder="+237 6..." required>
        <button type="submit" @disabled($plans->isEmpty())>Continuer vers le paiement</button>
    </form>
</main>
</body>
</html>
