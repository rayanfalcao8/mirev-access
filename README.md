# Mirev Access

Mirev Access is a hardware-independent customer access platform for managed Wi-Fi networks.

Its first release focuses on the customer journey:

1. Join a hotspot.
2. Identify with a phone number.
3. Choose a plan.
4. Pay through a supported provider.
5. Receive network access.
6. View and renew the active subscription.

## Product boundaries

Mirev Access owns customers, plans, orders, payments, subscriptions, devices, and access grants. Network hardware is isolated behind a `NetworkAccessProvider` contract.

Initial adapters:

- `FakeAccessProvider` for local development and automated tests.
- `RadiusAccessProvider` for production-compatible RADIUS networks.

Payment providers follow the same adapter pattern. The first milestone uses a fake payment provider before adding Mobile Money webhooks.

## Target stack

- Laravel 12
- Livewire client PWA
- Filament operator panel
- PostgreSQL
- Redis and queues
- FreeRADIUS through a dedicated adapter

## First milestone

Deliver a tested end-to-end demo that works without physical networking equipment:

- browse site plans;
- identify a customer;
- create an order;
- simulate a successful payment webhook;
- activate a subscription;
- grant access through the fake adapter;
- show the active plan and expiry;
- renew the subscription.

## Local bootstrap

The Laravel skeleton must be generated with the local PHP and Composer runtime so dependency resolution and the lockfile are reproducible. See [docs/bootstrap.md](docs/bootstrap.md).

## Status

Product foundation in progress. No production deployment yet.
