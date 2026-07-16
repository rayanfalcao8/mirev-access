# Client architecture

## Core rule

The client application and business domains must not import a Ruijie, Reyee, MikroTik, or other vendor SDK directly.

```text
Client PWA
    -> Application API
        -> Orders / Payments / Subscriptions
            -> NetworkAccessProvider
                -> Fake adapter
                -> RADIUS adapter
                -> Future vendor adapters
```

## Initial domains

- Identity: phone-based customer identity and OTP lifecycle.
- Sites: hotspot identity, locale, currency, and available plans.
- Plans: price, validity, speed policy, data policy, and device allowance.
- Orders: commercial intent before payment.
- Payments: provider-neutral attempts and idempotent webhook processing.
- Subscriptions: entitlement lifecycle and expiry.
- Devices: customer-owned network device references.
- Access: provider-neutral grants, sessions, and revocation.

## Provider contract

```php
interface NetworkAccessProvider
{
    public function authorize(AccessGrantData $grant): AccessResult;

    public function disconnect(NetworkSessionData $session): AccessResult;

    public function updatePolicy(
        NetworkSessionData $session,
        AccessPolicyData $policy,
    ): AccessResult;

    public function usage(NetworkSessionData $session): UsageData;

    public function health(): ConnectorStatus;
}
```

Domain actions depend on this interface. Provider selection belongs to infrastructure configuration at the site level.

## First vertical slice

```text
Plan selection
    -> order creation
    -> fake payment confirmation
    -> subscription activation
    -> fake network authorization
    -> customer dashboard
```

## Non-negotiable safeguards

- Payment confirmation comes from a verified, idempotent webhook.
- A URL parameter alone cannot authorize network access.
- Hardware identifiers are normalized and stored separately from customer identity.
- Provider-specific payloads remain in infrastructure adapters.
- Expired subscriptions cannot create new access grants.
- Replayed payment webhooks cannot activate multiple subscriptions.
