# Architecture Decision Records (ADRs)

This directory records key architectural decisions, trade-offs, and infrastructure assumptions for the **Infinity** backend (the backend of the **INFINITE Dashboard** project for the **INFINITE** organization) so maintainers and auditors share the same context.

Each ADR follows a concise structure:

- **Status:** `Proposed` | `Accepted` | `Deprecated` | `Superseded`
- **Date:** `YYYY-MM-DD`
- **Context:** Forces, constraints, and problem statement driving the decision.
- **Decision:** Chosen architecture and how it is wired in code/configuration.
- **Consequences:** Resulting guarantees, operational invariants, and trade-offs.

---

## Index

| ADR                                                                     | Title                                                                                          | Status   | Date       |
| ----------------------------------------------------------------------- | ---------------------------------------------------------------------------------------------- | -------- | ---------- |
| [ADR-0001](./0001-local-disk-private-blob-serving.md)                   | Local-Disk Private Blob Serving & Default Private Storage Visibility                           | Accepted | 2026-11-27 |
| [ADR-0002](./0002-istio-gateway-oidc-headers-authentication.md)         | Offloading OIDC Token Validation to Istio API Gateway (`OidcHeadersGuard`)                     | Accepted | 2026-11-27 |
| [ADR-0003](./0003-redis-backed-maintenance-mode-cache-store.md)         | Redis-Backed Maintenance Mode with Local/Docs File Fallback                                    | Accepted | 2026-11-27 |
| [ADR-0004](./0004-team-permissions-leadership-and-individual-teams.md)  | Team Permissions, Leadership Transfer, Membership Rules, and Automated Individual Teams        | Accepted | 2026-11-27 |
| [ADR-0005](./0005-nullable-blob-field-upload-and-deletion-semantics.md) | Nullable Blob Field Upload and Deletion-by-Null Semantics                                      | Accepted | 2026-11-27 |
| [ADR-0006](./0006-authentik-sso-user-and-group-synchronization.md)      | Authentik SSO User and Group Synchronization                                                   | Accepted | 2026-11-27 |
| [ADR-0007](./0007-outline-docs-group-and-collection-synchronization.md) | Outline Docs Group and Collection Synchronization                                              | Accepted | 2026-11-27 |
| [ADR-0008](./0008-group-integrations-table-for-external-tools.md)       | Dedicated `group_integrations` Table and Vendor-Agnostic Naming for External Tool Integrations | Accepted | 2026-11-27 |
| [ADR-0009](./0009-external-horizon-dashboard-authentication.md)         | Delegating Laravel Horizon Dashboard Authentication to External Infrastructure                 | Accepted | 2026-11-28 |
