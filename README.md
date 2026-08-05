# Storage Server

A lightweight, self-hosted S3-compatible object storage server built on
Laravel 12 — REST API + Admin Panel + Storage Engine, no public-facing
website. See [`docs/local/prd.md`](docs/local/prd.md) for the full
product spec.

## What's here

- **S3-compatible REST API** — `GET /`, `PUT/DELETE /{bucket}`,
  `GET/PUT/HEAD/DELETE /{bucket}/{object}`, HMAC access-key auth,
  pre-signed download URLs, streamed uploads/downloads.
  See [`docs/storage-api.md`](docs/storage-api.md).
- **Super Admin panel** (`/admin`) — system-wide stats, per-user storage
  breakdown, read-only bucket list, Settings, User Management.
- **Bucket Admin panel** (`/dashboard`, `/buckets`, `/objects`,
  `/api-keys`) — self-service bucket/object/API-key management, scoped
  to the logged-in user.
- Existing boilerplate auth (password, OTP, 2FA, passkeys, Google OAuth)
  is unchanged — see [`AGENTS.md`](AGENTS.md).

## Quick start

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan db:seed
php artisan db:seed --class="Database\Seeders\AuthSeeder"
php artisan serve
```

Full setup guide, troubleshooting, and first-bucket walkthrough:
[`docs/local/installation.md`](docs/local/installation.md).

## Docs

| Document | Contents |
|---|---|
| [`docs/local/prd.md`](docs/local/prd.md) | Product requirements |
| [`docs/local/installation.md`](docs/local/installation.md) | Install guide |
| [`docs/storage-api.md`](docs/storage-api.md) | S3 API reference, auth scheme, cURL examples |
| [`postman/Storage-API.postman_collection.json`](postman/Storage-API.postman_collection.json) | Postman collection (auto-signs requests) |
| [`docs/architecture.md`](docs/architecture.md) | Layering, request lifecycle |
| [`docs/authentication.md`](docs/authentication.md) | Panel session auth (unrelated to the S3 API's HMAC auth) |
| [`AGENTS.md`](AGENTS.md) | Working guide for the existing boilerplate |
