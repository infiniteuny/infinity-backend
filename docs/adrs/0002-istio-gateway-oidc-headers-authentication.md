# ADR-0002: Offloading OIDC Token Validation to Istio API Gateway (`OidcHeadersGuard`)

- **Status:** Accepted
- **Date:** 2026-11-27

## Context

Infinity authenticates users against Authentik (OpenID Connect / OAuth 2.0). Performing full cryptographic JWT verification (JWKS signature checks, issuer/audience validation) or OAuth token introspection inside Laravel on every protected API request adds latency and CPU overhead.

In production, Infinity runs inside a Kubernetes / Istio service mesh where the **Istio API Gateway** (Envoy + auth filter) sits in front of the backend, validates incoming Bearer JWTs (signature, claims, and `exp` expiration), strips client-spoofed internal headers, and injects trusted identity/token metadata headers (`X-User-Id`, `X-User-Email`, `X-Token-Id`, `X-Token-Created-At`, `X-Token-Expires-At`).

At the same time:

1. Users and administrators need the ability to list active sessions and **revoke tokens early** (`GET /v1/tokens`, `DELETE /v1/tokens/{token}`) before the JWT's `exp` elapses.
2. Semi-public endpoints (`@unauthenticated` routes such as `GET /v1/users` or `GET /v1/achievements`) allow unauthenticated access through the gateway while optionally unredacting fields when a valid Bearer token is presented, and local/non-mesh environments may not run Istio.

## Decision

1. **Protected routes behind Istio use `OidcHeadersGuard` (`api_headers`):**
    - `OidcHeadersGuard` trusts that Istio has already verified the Bearer token's cryptographic signature, subject/email binding, and `exp` timestamp before forwarding `X-User-*` and `X-Token-*` headers to the backend.
    - On the first request seen for a given `X-Token-Id`, `OidcHeadersGuard` introspects the token once against Authentik (`OidcService::introspect()`) to confirm it has not been revoked upstream and persists a `Token` record locally.
    - On subsequent requests with an already-recorded `X-Token-Id`, `OidcHeadersGuard` intentionally skips redundant JWT signature and `expires_at` checks — it only checks local soft-delete revocation (`$token->trashed()`) and updates `last_used_at`.
2. **Direct Bearer verification uses `OidcTokenGuard` (`api_token`):**
    - Where Istio does not pre-validate tokens (such as `AUTH_SEMI_PUBLIC_GUARD=api_token` on semi-public routes or standalone deployments without Istio), `OidcTokenGuard` verifies the JWT signature and mandatory claims (`iss`, `sub`, `aud`, `exp`, `iat`) in-process via `Facile\JoseVerifier\JWTVerifier` on every request, alongside the same local `tokens` revocation table.

## Consequences

- **Positive:** Near-zero per-request auth overhead on gateway-protected routes while retaining immediate server-side session revocation (`tokens.deleted_at`) and `last_used_at` tracking.
- **Security Invariant (Critical):** When `AUTH_GUARD=api_headers` is active, **the backend container port (`8000`) must never be directly reachable from untrusted networks bypassing Istio**, and Istio must overwrite or strip incoming `X-User-*` / `X-Token-*` headers from external clients. Deployments without a trusted authenticating gateway must use `AUTH_GUARD=api_token` instead.
