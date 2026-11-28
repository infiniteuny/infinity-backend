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

1. **Istio Ambient Waypoint + `OidcHeadersGuard` (`api_headers`):**
    - In production, `RequestAuthentication` targets `Service/infinity-backend` via `istio-waypoint` (`forwardOriginalToken: true`, `outputClaimToHeaders` -> `x-user-id`, `x-user-email`, `x-token-id`, `x-token-created-at`, `x-token-expires-at`), validating incoming Bearer JWTs across **both** protected routes (`AUTH_GUARD=api_headers`) and `@unauthenticated` semi-public routes (`AUTH_SEMI_PUBLIC_GUARD=api_headers`).
    - `OidcHeadersGuard` strictly parses `Authorization: Bearer <token>` at offset `0` (matching Envoy's `jwt_authn` extractor so non-`Bearer` prefixes cannot slip past the gateway), validates numeric/unexpired `X-Token-*` headers, and restricts opportunistic email fallback to unbound local users (`sso_id IS NULL`).
    - On the first request seen for a given `X-Token-Id`, `OidcHeadersGuard` introspects the token once against Authentik (`OidcService::introspect()`), verifying `active`, `exp > now`, `sub === X-User-Id` (and `uid === X-Token-Id` when present) before persisting a `Token` record locally.
    - On subsequent requests with an already-recorded `X-Token-Id`, `OidcHeadersGuard` skips redundant JWT signature checks and verifies local revocation (`$token->trashed()`) and expiration (`$token->expires_at`).
2. **Direct Bearer verification (`OidcTokenGuard` / `api_token`):**
    - In standalone or local deployments without an authenticating Istio gateway (`AUTH_GUARD=api_token` / `AUTH_SEMI_PUBLIC_GUARD=api_token`), `OidcTokenGuard` verifies the asymmetric JWT signature and mandatory claims (`iss`, `sub`, `aud`, `exp`, `iat`) in-process via `Facile\JoseVerifier\JWTVerifier` on every request, alongside the same local `tokens` revocation table.

## Consequences

- **Positive:** Near-zero per-request auth overhead behind Istio Waypoint while retaining immediate server-side session revocation (`tokens.deleted_at`), `last_used_at` tracking, and defense-in-depth subject/email binding checks.
- **Security Invariant (Critical):** When `api_headers` is active, **the backend container port (`8000`) must never be directly reachable from untrusted networks bypassing `istio-waypoint`**, and the ingress `HTTPRoute` should strip incoming client-supplied `x-user-id`, `x-user-email`, `x-token-id`, `x-token-created-at`, and `x-token-expires-at` headers (`RequestHeaderModifier`) as defense-in-depth alongside `OidcHeadersGuard`'s strict offset-`0` `Bearer` parsing and introspection subject checks. Deployments without a trusted authenticating gateway must use `api_token` instead.
