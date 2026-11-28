# ADR-0010: Stateless Application Architecture Without HTTP Sessions

- **Status:** Accepted
- **Date:** 2026-11-28

## Context

Infinity is a strictly **stateless, API-first** Laravel backend:

1. Client authentication relies exclusively on stateless OIDC Bearer access tokens (`OidcHeadersGuard` behind Istio and `OidcTokenGuard` for direct JWT verification — [ADR-0002](./0002-istio-gateway-oidc-headers-authentication.md)), with cookie/credentialed cross-origin requests disabled (`'supports_credentials' => false` in `config/cors.php`).
2. Neither the `api` middleware group nor the `web` middleware group (which serves only static Scribe documentation artifacts in `routes/web.php`) registers `StartSession`, `EncryptCookies`, or `VerifyCsrfToken` in `bootstrap/app.php`, and Horizon dashboard access control is delegated to external gateway/infrastructure boundaries ([ADR-0009](./0009-external-horizon-dashboard-authentication.md)).
3. No `sessions` database migration exists in `database/migrations/`, and no controller, guard, or job reads or writes HTTP session state.

Because Laravel's framework fallback configuration defaults `session.driver` to `database` when `config/session.php` is absent, code auditors can mistake the absence of `config/session.php` and `SESSION_DRIVER` for an accidental omission.

## Decision

1. **Omit `config/session.php` and `SESSION_*` environment variables:**
    - Infinity intentionally does **not** maintain a `config/session.php` file or `SESSION_*` environment variables; session configuration is not required for this application.
2. **Enforce stateless HTTP and Octane worker lifecycles:**
    - `bootstrap/app.php` keeps all middleware groups free of session, cookie encryption, and CSRF middleware.
    - `config/octane.php` excludes `'session'` and `'session.store'` from `warm` bindings so FrankenPHP/Octane workers do not instantiate unused session services on boot.

## Consequences

- **Positive:** Zero stateful session storage dependencies (no `sessions` table or Redis session keys), reduced configuration surface, and frictionless horizontal scaling across stateless FrankenPHP/Octane pods.
- **Architectural Invariant:** Application code (`app/`, `routes/`, `bootstrap/`) **must not** invoke `$request->session()` or `session()`, register `StartSession`, or introduce cookie/session-based guards unless this ADR is formally superseded.
