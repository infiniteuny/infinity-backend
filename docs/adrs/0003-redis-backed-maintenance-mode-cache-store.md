# ADR-0003: Redis-Backed Maintenance Mode with Local/Docs File Fallback

- **Status:** Accepted
- **Date:** 2026-11-27

## Context

Laravel's maintenance mode (`php artisan down` / `php artisan up`, checked globally by `PreventRequestsDuringMaintenance`) supports two drivers:

- `file` — writes a local payload under `storage/framework/down` (single-container only).
- `cache` — stores the maintenance payload in a named cache store from `config/cache.php` (`APP_MAINTENANCE_STORE`), sharing state across horizontally scaled replicas.

Laravel 11+'s upstream skeleton defaults `APP_MAINTENANCE_STORE` to `'database'` (referring to Laravel's SQL `cache` table driver in `config/cache.php`). However, Infinity intentionally uses **Redis** for shared caching/queues in production and does **not** define a `'database'` cache store in `config/cache.php` — causing `'store' => 'database'` to throw `InvalidArgumentException: Cache store [database] is not defined`. Meanwhile, local SQLite development and CI Scribe documentation builds (`php artisan scribe:generate --env docs`) run without a Redis server.

## Decision

1. **Production default (`config/app.php`):**
    - Default `app.maintenance.driver` to `'cache'` and `app.maintenance.store` to `'redis'` (resolving the `'redis'` store in `config/cache.php`).
2. **Local & CI documentation override (`.env.example`, `.env.docs`, `phpunit.xml`):**
    - Set `APP_MAINTENANCE_DRIVER=file` in `.env.example`, `.env.docs`, and `phpunit.xml` so local development, Scribe OpenAPI generation, and PHPUnit tests run with zero Redis dependency.

## Consequences

- **Positive:** Running `php artisan down` in production propagates immediately across all Octane pods via Redis without requiring shared filesystem volumes or a SQL `cache` table, while local and CI workflows work out of the box.
- **Trade-offs:** Production deployments using the default `cache` maintenance driver require a reachable Redis `cache` connection (already required by Horizon and application caching).
