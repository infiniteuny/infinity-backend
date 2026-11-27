# ADR-0006: Authentik SSO User and Group Synchronization

- **Status:** Accepted
- **Date:** 2026-11-27

## Context

The **INFINITE** organization separates **authentication (SSO)** from **user & organization lifecycle management**:

- **Authentik** (`auth.infiniteuny.id`, integrated via `App\Services\AuthentikService` / `/api/v3/core/*`) serves strictly as INFINITE's centralized **SSO authentication engine** (login flows, credentials/MFA, OIDC / OAuth 2.0 token issuance per [ADR-0002](./0002-istio-gateway-oidc-headers-authentication.md), and SSO group claims for connected internal applications such as Outline Docs). Users do not edit their profiles inside Authentik — **Authentik's profile settings UI redirects directly to the profile settings page on the INFINITE Dashboard** (project codename **Infinity**).
- **Infinity** (the INFINITE Dashboard project, consisting of this backend and the frontend) is the single control plane where users and administrators provision accounts, manage user profile attributes (`name`, `username`, `email_address`, academic/contact details), govern membership lifecycles (`is_member`, `start_date`, `end_date`), and manage RBAC groups (`App\Models\Group`) and yearly organizational cohorts (`App\Models\CoreTeam`, `App\Models\CommunityGroupAdmin`).

Because all provisioning and profile edits happen in Infinity while login and OIDC claims are served by Authentik, the synchronization architecture must address:

1. Keeping Authentik accounts, profile attributes, and OIDC group claims (`Core Team`, `Ex Core Team`, `CG Admin`, `Ex CG Admin`, `Member`, `Active Member`, `Inactive Member`, `Administrator`) continuously aligned with Infinity's database.
2. Adopting pre-existing Authentik users/groups without duplication while preserving attributes configured directly in Authentik (`parents`, `is_superuser`) when Infinity performs partial updates.
3. Keeping API mutations fast and resilient without blocking HTTP requests on synchronous upstream calls to Authentik.

## Decision

### 1. One-Way Outbound Authoritative Sync with Idempotent Link-Back

- **Infinity is the authoritative source of truth** for user profiles, account activation (`is_member`), group definitions, and group memberships. Changes flow **outbound** to Authentik asynchronously via queued jobs (`CreateSsoUser`, `UpdateSsoUser`, `DeleteSsoUser`, `CreateSsoGroup`, `UpdateSsoGroup`, `DeleteSsoGroup`) with `$tries = 5`.
- **Inline sync metadata (central auth vs. external tools):** Because Authentik is INFINITE's core, permanent authentication layer across all users and RBAC groups (unlike replaceable external workspace tools integrated via `group_integrations` — see [ADR-0008](./0008-group-integrations-table-for-external-tools.md)), SSO sync state is stored directly on `users` (`sso_id`, `sso_last_synced_at`) and `groups` (`sso_id`, `sso_last_synced_at`) and hidden from public API serialization (`$hidden`).
    - Authentik **User** primary keys (`pk`) are integers (stored as strings in `users.sso_id` and cast to `int[]` when serializing group member lists in `App\Data\Authentik\Group::fromModel()`).
    - Authentik **Group** primary keys (`pk`) are UUID strings (stored in `groups.sso_id` and referenced in `parents`).
- **Idempotent adoption of pre-existing records:**
    - `CreateSsoUser` queries Authentik by email (`GET /api/v3/core/users/?email=...`) and updates/links the existing Authentik user if found before falling back to `POST /api/v3/core/users/`.
    - `OidcHeadersGuard` and `OidcTokenGuard` opportunistically link `users.sso_id` by matching `email_address` when a local user logs in via OIDC before `CreateSsoUser` has run.
    - `CreateSsoGroup` updates the pre-bound `groups.sso_id` first (falling back on `404`), otherwise searches `GET /api/v3/core/groups/?name=...` for an exact name match to adopt before creating a new Authentik group.
    - `DeleteSsoUser` and `DeleteSsoGroup` treat HTTP `404` responses from Authentik as idempotent success.

### 2. User Attribute & Organizational Path Classification

`App\Data\Authentik\User` maps local `App\Models\User` state into Authentik user accounts as follows:

| Infinity `User` Field / State | Authentik `User` Field | Mapping / Value                                           |
| ----------------------------- | ---------------------- | --------------------------------------------------------- |
| `sso_id`                      | `pk`                   | Linked Authentik integer user PK                          |
| `name`                        | `name`                 | Full display name                                         |
| `username`                    | `username`             | Unique handle                                             |
| `email_address`               | `email`                | Primary email address                                     |
| `is_member`                   | `is_active`            | `true` for registered members; `false` disables SSO login |
| `is_member === true`          | `path` / `type`        | `path = "infiniteuny.id"`, `type = "internal"`            |
| `is_member === false`         | `path` / `type`        | `path = "uny.ac.id"`, `type = "external"`                 |

### 3. Two-Stage Group Pipeline & Watermark Dirty-Tracking

Synchronization of group memberships operates in two decoupled stages:

1. **Stage 1 — Local Managed Group Derivation (Hourly):**
    - `SyncMemberGroup` syncs `Group('Member')` with all `users` where `is_member = true`.
    - `SyncActiveInactiveMemberGroup` partitions `is_member = true` users by `[start_date, end_date]` relative to `today` into `Group('Active Member')` and `Group('Inactive Member')`.
    - `SyncCoreTeamMembersGroup` syncs each `CoreTeam`'s `Core Team {year}` group with `core_team_members` and reconciles aggregate `Group('Core Team')` vs. `Group('Ex Core Team')`.
    - `SyncCGAdminMembersGroup` syncs each `CommunityGroupAdmin`'s `CG Admin {year}` group with `community_group_admin_members` and reconciles aggregate `Group('CG Admin')` vs. `Group('Ex CG Admin')`.
2. **Stage 2 — Dirty-Tracked Outbound SSO Dispatch (Immediate + Hourly):**
    - Pivot and cohort mutations (`UserGroup`, `CoreTeam`, `CommunityGroupAdmin`) declare `$touches = ['group']`, bumping `groups.updated_at` whenever memberships or cohort metadata change.
    - Direct CRUD endpoints (`UserController`, `GroupController`) dispatch `Create/Update/DeleteSsoUser` and `Create/Update/DeleteSsoGroup` immediately for low latency, while hourly scheduled orchestrators (`SyncSsoUsers` and `Sync*SsoGroup*`) compare `sso_last_synced_at` against `updated_at`:
        - `sso_last_synced_at === null` → `CreateSsoUser` / `CreateSsoGroup`
        - `sso_last_synced_at < updated_at` → `UpdateSsoUser` / `UpdateSsoGroup`
    - Group membership is pushed as a **full snapshot** (`users: int[]` on `POST`/`PATCH /api/v3/core/groups/{pk}/`), atomically adding new members and removing detached members in one request.

### 4. Authentik Group Hierarchy (`parents`) & Superuser Mapping

Infinity leverages Authentik's native **nested group inheritance (`parents`)** and **`is_superuser`** flag via `config('services.authentik.*')`:

| Local `Group`                           | Orchestrator                       | Pre-bound `sso_id` Config   | Authentik `parents` (`sso_parent_ids`)    | `is_superuser` |
| --------------------------------------- | ---------------------------------- | --------------------------- | ----------------------------------------- | -------------- |
| `Administrator`                         | `SyncAdminSsoGroup`                | `AUTHENTIK_ADMIN_GROUP_ID`  | `Optional` _(preserves existing parents)_ | `true`         |
| `Member`                                | `SyncMemberSsoGroup`               | `AUTHENTIK_MEMBER_GROUP_ID` | `[AUTHENTIK_INTERNAL_GROUP_ID]`           | `Optional`     |
| `Active Member` / `Inactive Member`     | `SyncActiveInactiveMemberSsoGroup` | —                           | `[AUTHENTIK_MEMBER_GROUP_ID]`             | `Optional`     |
| `Core Team {year}` (`is_active: true`)  | `SyncCoreTeamSsoGroups`            | —                           | `[AUTHENTIK_CORE_TEAM_GROUP_ID]`          | `Optional`     |
| `Core Team {year}` (`is_active: false`) | `SyncXCoreTeamSsoGroups`           | —                           | `[AUTHENTIK_XCORE_TEAM_GROUP_ID]`         | `Optional`     |
| `CG Admin {year}` (`is_active: true`)   | `SyncCGAdminSsoGroups`             | —                           | `[AUTHENTIK_CG_ADMIN_GROUP_ID]`           | `Optional`     |
| `CG Admin {year}` (`is_active: false`)  | `SyncXCGAdminSsoGroups`            | —                           | `[AUTHENTIK_XCG_ADMIN_GROUP_ID]`          | `Optional`     |
| Custom `Group` (`is_managed: false`)    | `GroupController`                  | —                           | `Optional` _(preserves existing parents)_ | `Optional`     |

When a `CoreTeam` or `CommunityGroupAdmin` cohort transitions from `is_active = true` to `is_active = false`, `SyncXCoreTeamSsoGroups` / `SyncXCGAdminSsoGroups` patches `parents` on the cohort's Authentik group from the active umbrella group to the `X*` alumni umbrella group. Whenever an orchestrator or controller omits `$ssoParentIds` or `$isSuperuser`, `Spatie\LaravelData\Optional` omits those keys from the `PATCH` JSON payload so attributes managed directly in Authentik are never overwritten.

## Consequences

- **Positive:**
    - API mutations remain fast and non-blocking while hourly watermark reconciliation self-heals missed dispatches or transient Authentik outages.
    - Downstream OIDC apps automatically inherit nested group memberships (`Internal`, `Member`, `Core Team`, `Ex Core Team`, `CG Admin`, `Ex CG Admin`) and Authentik `Administrator` superuser access without manual duplication.
- **Invariants to Maintain:**
    - **Execution ordering:** Stage 1 local group derivation jobs and `SyncSsoUsers` should execute before `Sync*SsoGroup*` jobs so `user_groups` is current and every synced `User` already has a non-null integer `sso_id`.
    - **Null-safe member serialization:** `App\Data\Authentik\Group::fromModel()` must exclude any local `User` whose `sso_id` is not yet populated rather than casting `null` or failing the group sync.
    - **Silent watermark persistence:** Updating `sso_id` and `sso_last_synced_at` after a successful sync should avoid unnecessarily advancing `updated_at` past `sso_last_synced_at` (which would cause redundant hourly re-syncs).
    - **Managed group lifecycle:** Managed groups (`groups.is_managed = true`) are blocked from manual rename/delete in `GroupPolicy`; any domain flow that deletes a cohort-owned `Group` (`CoreTeamController::destroy`, `CommunityGroupAdminController::destroy`) must also dispatch `DeleteSsoGroup` if `sso_id` is set.
