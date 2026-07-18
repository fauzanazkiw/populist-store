# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Overview

"populist" is a Laravel 13 (PHP 8.3) e-commerce application for a clothing store. It exposes the **same domain through two parallel interfaces**: a server-rendered web app (Blade + session auth) and a JSON API (Sanctum token auth). Understanding this dual-interface split is the key to navigating the codebase.

## Commands

```bash
# First-time setup (install deps, .env, key, migrate, build assets)
composer setup

# Run the full dev stack (server + queue + logs + vite) concurrently
composer dev

# Individual dev processes
php artisan serve          # PHP server
npm run dev                # Vite dev server (HMR)
npm run build              # Production asset build

# Tests (Pest)
composer test              # clears config, then runs the suite
php artisan test
./vendor/bin/pest --filter='test name or substring'   # single test / group
php artisan test tests/Feature/ExampleTest.php         # single file

# Lint / format
./vendor/bin/pint          # Laravel Pint (PSR-12 style fixer)

# Database
php artisan migrate
php artisan migrate:fresh --seed   # rebuild + seed demo data
```

## Architecture

### Dual interface, shared domain
Controllers are split into two namespaces that mirror each other:
- `App\Http\Controllers\Web\*` — return Blade views, use session `auth` middleware. Admin controllers live under `Web\Admin\*`.
- `App\Http\Controllers\Api\*` — return JSON, use `auth:sanctum`.

Routes follow the same split: `routes/web.php` and `routes/api.php` (both wired in `bootstrap/app.php`). When adding a feature, check whether it needs to exist on both surfaces.

### Business logic lives in services, not controllers
`App\Services\Api\ProductService` is the single source of truth for product mutations (create/update/delete, slug generation, stock changes, image storage). It wraps multi-step operations in `DB::transaction`, generates unique slugs, and manages image files on the `public` storage disk. Controllers should delegate here rather than manipulating models directly. (Despite the `Api` sub-namespace, it is the shared product service.)

### Stock and domain exceptions
Stock is mutated only via `ProductService::deductStock` / `restoreStock`. `deductStock` throws `App\Exceptions\InsufficientStockException` (carries `getAvailable()` / `getRequested()`) when stock is insufficient — handle this at the call site. Products with non-`completed`/`cancelled` orders cannot be deleted.

### Authorization
- Web admin routes are gated by the `is_admin` middleware alias → `App\Http\Middleware\EnsureUserIsAdmin` (checks `user->is_admin`, aborts 403). Registered in `bootstrap/app.php`.
- **Gotcha:** API admin routes in `routes/api.php` are only guarded by `auth:sanctum` — there is no admin-role check on the API side. Add one before treating API admin endpoints as protected.
- `App\Http\Middleware\AuthApiToken` and the `users.api_token` column are a legacy custom-token scheme **not registered** in `bootstrap/app.php`. The live API uses Sanctum; treat `AuthApiToken` as dead code unless you intentionally wire it up.

### Models
Standard Eloquent under `App\Models`: `User`, `Product`, `Category`, `ProductImage`, `Order`, `OrderItem`, `CartItem`. Note `Product::mainImage()` (hasOne where `is_main = true`) vs `images()` (hasMany). Migrations in `database/migrations` are manually numbered (`2026_05_25_00000N_*`) to enforce order.

### Database environments (inconsistent — verify before assuming)
- `.env.example` defaults to **MySQL** (`populist`).
- `docker-compose.yml` / `Dockerfile` provision **PostgreSQL** (`populistdb`, user `populist`).
- Tests run on **SQLite `:memory:`** (`phpunit.xml`).

Confirm which connection is active from the local `.env` before writing DB-specific SQL.

### Seed data
`DatabaseSeeder` creates an admin (`poran@example.com` / `poran123`) and a customer (`test@example.com` / `customer123456`), three categories, and demo products. The admin is intentionally seed-only — you cannot register an admin via API/web.

## Security note

`routes/web.php` currently contains injected non-PHP content (lines ~77–109): shell `curl`/`export` commands with a hardcoded third-party API key pointing at `capi.aerolink.lat`. This is not legitimate code, breaks the file as valid PHP, and the embedded key should be considered compromised. Do not execute those commands or route traffic through that endpoint — remove the block.
