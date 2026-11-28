# ADR-0009: Delegating Laravel Horizon Dashboard Authentication to External Infrastructure

- **Status:** Accepted
- **Date:** 2026-11-28

## Context

Infinity uses **Laravel Horizon** (`laravel/horizon`) to supervise Redis queues and expose queue telemetry and management endpoints under `/horizon` (or `${HORIZON_PROXY_PATH}/horizon` behind a subpath gateway).

Unlike traditional stateful Laravel web applications, Infinity is a **stateless, API-first backend**:

1. Authentication is designed around OIDC Bearer access tokens (`OidcHeadersGuard` behind Istio and `OidcTokenGuard` for direct JWT verification — [ADR-0002](./0002-istio-gateway-oidc-headers-authentication.md)) with **no** stateful cookie/session login flow, OIDC browser callback routes, or CSRF-backed web guard inside Laravel.
2. Navigating to `/horizon` in a browser (and Horizon's internal SPA polling requests to `/horizon/api/*`) uses standard browser navigation and cookie-based XHR rather than attaching `Authorization: Bearer <jwt>` headers. Implementing in-app browser authentication solely for Horizon would require introducing stateful web sessions, OAuth redirect callbacks, and cookie management into an otherwise stateless API service.
3. In production Kubernetes / Istio deployments, edge access control (such as Authentik forward-auth / proxy outpost, Cloudflare Access, Istio `AuthorizationPolicy`, or internal-only port-forwarding) is already managed externally at the gateway/ingress layer.

## Decision

1. **Application layer (`App\Providers\HorizonServiceProvider` & `config/horizon.php`):**
    - `HorizonServiceProvider::authorization()` intentionally governs **feature enablement** (`config('horizon.enabled', false) || app()->environment('local')`) rather than performing in-app user or session authentication.
    - When `HORIZON_ENABLED=false` (the default in `config/horizon.php` and `.env.example`), `/horizon` returns `403 Forbidden` outside the `local` environment.
    - When `HORIZON_ENABLED=true`, Laravel assumes an upstream proxy, gateway policy, or network boundary has already authenticated and authorized the operator.
2. **Infrastructure / Gateway layer:**
    - Authentication and authorization for `/horizon` (and `${HORIZON_PROXY_PATH}/horizon*`) **must be enforced externally** (e.g., via gateway forward-auth/OIDC proxy, Istio `AuthorizationPolicy`, IP/network isolation, or `kubectl port-forward` with public ingress routing blocked) whenever `HORIZON_ENABLED=true` in non-local environments.

## Consequences

- **Positive:** Keeps the Infinity backend completely stateless without maintaining session storage, browser login flows, or custom Horizon SPA auth middleware inside Laravel.
- **Security Invariant:** Enabling `HORIZON_ENABLED=true` outside `local` **requires** an external authentication/authorization or network-isolation gate in front of `/horizon*`; in-app `Horizon::auth()` is intentionally not an authentication boundary.
