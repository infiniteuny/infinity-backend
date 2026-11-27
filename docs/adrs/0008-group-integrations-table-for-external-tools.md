# ADR-0008: Dedicated `group_integrations` Table and Vendor-Agnostic Naming for External Tool Integrations

- **Status:** Accepted
- **Date:** 2026-11-27

## Context

In the **Infinity** backend, `App\Models\Group` serves three distinct concerns across the **INFINITE** organization:

1. **Application RBAC:** Grouping permissions (`role_has_permissions`) and users (`user_groups`) via Spatie Permission.
2. **Central SSO:** Synchronizing organization-wide groups and hierarchies to **Authentik** ([ADR-0006](./0006-authentik-sso-user-and-group-synchronization.md)).
3. **External productivity tool provisioning:** Synchronizing specific organizational groups (such as `Core Team 20xx`) to downstream tools deployed by INFINITE — starting with **Outline Community Edition** for internal documentation ([ADR-0007](./0007-outline-docs-group-and-collection-synchronization.md)), with additional external tools (e.g., chat, project management, code hosting, or shared drives) expected over time.

Putting external tool IDs and sync timestamps directly onto the `groups` table — or naming schema columns after a specific vendor product (such as `outline_*`) — creates three architectural problems:

1. **Tool replaceability:** Unlike Authentik (which is INFINITE's core, permanent SSO provider), downstream productivity tools such as Outline may be replaced in the future. Vendor-named columns on core tables leak replaceable implementation choices into the domain schema.
2. **Sparse, multi-resource columns on a hot RBAC table:** `groups` is queried constantly during request authorization, whereas external tool sync typically applies only to a subset of groups and often tracks multiple remote resources and watermarks per tool (for example, both a remote group ID and a remote collection/workspace ID).
3. **Inconsistent future integrations:** Without a designated integration table and naming convention, each new tool integration risks inventing ad-hoc columns on `groups` or scattered per-vendor mapping tables.

## Decision

### 1. Inline `sso_*` Columns Are Reserved Strictly for Central Authentication

- Only INFINITE's central SSO engine (Authentik — [ADR-0006](./0006-authentik-sso-user-and-group-synchronization.md)) stores foreign identifiers and sync watermarks directly on `users` (`sso_id`, `sso_last_synced_at`) and `groups` (`sso_id`, `sso_last_synced_at`).
- No downstream productivity or workspace tool may add columns directly to `users` or `groups`.

### 2. Shared `group_integrations` Table (`1:1` with `groups`) for External Tools

- External tool state for groups is consolidated in the **`group_integrations`** table (`App\Models\GroupIntegration`), linked `1:1` to `groups` via a `unique` `group_id` foreign key (`restrictOnUpdate`, `cascadeOnDelete`) and exposed via `Group::integration(): HasOne` (hidden from public API serialization via `$hidden`).
- Rows in `group_integrations` are created lazily (`GroupIntegration::firstOrNew(['group_id' => $group->id])`) **only** for groups that actively sync to at least one external tool.
- Future group-level tool integrations **must extend `group_integrations`** with `nullable` remote resource ID (`unique` where appropriate) and `*_last_synced_at` timestamp columns rather than adding columns to `groups` (unless a future tool genuinely requires a `1:N` mapping per `Group`).

### 3. Capability-Prefixed, Vendor-Agnostic Naming (`<capability>_*`)

- Columns on `group_integrations` and corresponding sync job names use **functional capability prefixes** rather than vendor names:
    - Documentation tool (currently Outline): `docs_group_id`, `docs_collection_id`, `docs_group_last_synced_at`, `docs_collection_last_synced_at`, paired with `CreateDocsGroup`, `UpdateDocsGroup`, `CreateDocsCollection`, `UpdateDocsCollection`, and `Sync*Docs*`.
    - Future tools follow the same `<capability>_*` pattern (e.g., `chat_*`, `git_*`, `pm_*`).
- Vendor-specific endpoints, DTOs, and credentials stay encapsulated in `App\Services\*`, `App\Data\*`, and `config/services.php` (e.g., `OutlineService`, `App\Data\Outline\*`, `config('services.outline.*')`).

## Consequences

- **Positive:**
    - `groups` remains clean and focused on RBAC and central SSO without sparse `NULL` columns as more tools are integrated.
    - Replacing an external tool (such as migrating from Outline to another documentation platform) only requires swapping the service adapter and job logic while reusing the vendor-agnostic `docs_*` integration columns.
    - Future tool integrations have an explicit, standardized home (`group_integrations`) and watermark convention (`<capability>_*_last_synced_at`).
- **Invariants to Maintain:**
    - All tool-specific columns added to `group_integrations` must be `nullable` so integrating a group with one tool never requires populating columns for unrelated tools.
    - Sync orchestrators reading tool watermarks must eager-load `integration` (`Group::with('integration')`) to avoid N+1 queries.
    - Because `group_integrations.group_id` uses `ON DELETE CASCADE`, any deletion flow that needs to clean up upstream tool resources must read `$group->integration` **before** deleting the local `Group` record (while respecting per-tool data retention rules such as preserving `docs_collection_id` in [ADR-0007](./0007-outline-docs-group-and-collection-synchronization.md)).
