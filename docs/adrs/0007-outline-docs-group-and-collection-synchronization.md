# ADR-0007: Outline Docs Group and Collection Synchronization

- **Status:** Accepted
- **Date:** 2026-11-27

## Context

Alongside the **INFINITE Dashboard** (codenamed **Infinity**, consisting of this backend and the frontend), the **INFINITE** organization deploys **Outline Community Edition** (`docs.infiniteuny.id`, integrated via `App\Services\OutlineService` / RPC `POST /api/<resource>.<action>`) for its internal documentation.

Although Outline authenticates users via INFINITE's Authentik OIDC ([ADR-0006](./0006-authentik-sso-user-and-group-synchronization.md)), two product requirements drive a dedicated synchronization pipeline from the Infinity backend to Outline:

1. **Outline Community Edition lacks OIDC group sync:** Even though Infinity already syncs groups and hierarchies to Authentik, Outline Community Edition cannot import or synchronize group memberships from OIDC claims — groups and memberships must be provisioned directly via Outline's API.
2. **Automated per-cohort collection & access provisioning:** Whenever a `CoreTeam` cohort (`Core Team 20xx`) is managed in Infinity, Outline must automatically provision its cohort group and its dedicated `Core Team 20xx` collection so that:
    - Current active Core Team members are also placed in the **`Core Team`** group and granted **View** (`read`) access across all `Core Team 20xx` collections.
    - Non-active Core Team members (who are not in the active Core Team) are placed in the **`Ex Core Team`** group and also granted **View** (`read`) access across all `Core Team 20xx` collections.
    - **Only** the matching **`Core Team 20xx`** group is granted **Editor** (`read_write`) access on its own `Core Team 20xx` collection.

Implementing this against Outline's API differs structurally from Authentik sync:

1. **External tool integration boundary:** As an external tool rather than central authentication, Outline sync state belongs in `group_integrations` under vendor-agnostic `docs_*` columns rather than on `groups` (see [ADR-0008](./0008-group-integrations-table-for-external-tools.md)).
2. **Dual remote resources per cohort group:** Each synced local `Group` owns **both** an Outline **Group** (for membership) and a private Outline **Collection** (for cohort documents), whereas general RBAC groups (`Member`, `Administrator`, custom groups) have no Outline counterpart.
3. **Incremental user & membership APIs:** Outline does not accept a bulk member snapshot on `groups.update`; users must be resolved or invited by email (`users.list`, `users.invite`) and added/removed individually (`groups.memberships`, `groups.add_user`, `groups.remove_user`).
4. **Flat group model (no parent/nested groups):** Outline groups cannot inherit from parent groups, so Infinity must directly assign users to both their cohort group (`Core Team 20xx`) and either `Core Team` or `Ex Core Team` — **without** demoting multi-year members who belong to both a non-active past cohort and the current active cohort.

## Decision

### 1. Group → Collection Watermark Tracking & 404 Self-Healing

- Per [ADR-0008](./0008-group-integrations-table-for-external-tools.md), `App\Models\GroupIntegration` tracks Outline's two remote resources and watermarks per synced `Group`: `docs_group_id`, `docs_group_last_synced_at`, `docs_collection_id`, and `docs_collection_last_synced_at`.
- **Strict Group → Collection dependency & 404 self-healing:**
    - `SyncCoreTeamDocsCollections` (and `CreateDocsCollection` / `UpdateDocsCollection`) runs **only** after `integration.docs_group_id` is populated.
    - If `UpdateDocsGroup` encounters HTTP `404` on `groups.update` (or `CreateDocsGroup` binds a different Outline group ID), it re-finds/recreates the Outline group by name, updates `docs_group_id`, and resets `docs_collection_last_synced_at = null` so the collection sync re-grants `read_write` to the new group ID.
    - Conversely, if `CreateDocsCollection` or `UpdateDocsCollection` receives HTTP `404` when granting `docs_group_id` access to the collection, it resets `docs_group_id = null` and `docs_group_last_synced_at = null` (and `docs_collection_last_synced_at = null`) so the next group sync cycle recreates the missing group before collection sync retries.
    - If `UpdateDocsCollection` receives HTTP `404` on `collections.update`, it re-finds or recreates the collection by exact name and updates `docs_collection_id`.

### 2. Email-Normalized User Resolution & Auto-Invitation

- Infinity does **not** persist an Outline user ID column on `users`. Instead, `CreateDocsGroup` and `UpdateDocsGroup` resolve local `Group` users carrying valid email addresses against Outline dynamically by normalized (`strtolower(trim(...))`) email:
    1. **Batch lookup:** Query `POST /api/users.list` with `emails` in chunks of `100` (`filter: "all"`, matching active, invited, and suspended accounts).
    2. **Batch auto-invite:** Any local user not yet present in Outline is invited in batches of `20` via `POST /api/users.invite` (`role: "member"`, optional `suppressInviteEmail`).
    3. **Fallback verification:** If an invited email is not returned in `users.invite` (e.g., race or pre-existing state), a single-email `users.list` lookup resolves it or throws a `RuntimeException` so the job retries rather than silently dropping a member.

### 3. Cohort, `"Core Team"`, and `"Ex Core Team"` Group Assignment

Because Outline groups are flat, `CreateDocsGroup` and `UpdateDocsGroup` assign each member to their cohort's `Core Team {year}` group **and** reconcile their membership in the shared `"Core Team"` (`OUTLINE_CORE_TEAM_GROUP_ID`) and `"Ex Core Team"` (`OUTLINE_XCORE_TEAM_GROUP_ID`) groups via `$docsParentIds` and `$docsRemoveParentIds`:

| Cohort State                     | Orchestrator              | Cohort Outline Group | Shared Group Added (`$docsParentIds`)              | Shared Group Removed (`$docsRemoveParentIds`)      |
| -------------------------------- | ------------------------- | -------------------- | -------------------------------------------------- | -------------------------------------------------- |
| `CoreTeam` (`is_active = true`)  | `SyncCoreTeamDocsGroups`  | `Core Team {year}`   | `"Core Team"` (`[OUTLINE_CORE_TEAM_GROUP_ID]`)     | `"Ex Core Team"` (`[OUTLINE_XCORE_TEAM_GROUP_ID]`) |
| `CoreTeam` (`is_active = false`) | `SyncXCoreTeamDocsGroups` | `Core Team {year}`   | `"Ex Core Team"` (`[OUTLINE_XCORE_TEAM_GROUP_ID]`) | `"Core Team"` (`[OUTLINE_CORE_TEAM_GROUP_ID]`)     |

- **Global Multi-Cohort Invariant (`shouldUserBelongToSharedGroup`):** Because a user may belong to multiple cohorts across years (e.g., non-active `Core Team 2024` **and** active `Core Team 2025`), `"Core Team"` vs. `"Ex Core Team"` membership is evaluated across **all** of the user's cohorts rather than the single cohort being synced:
    - Assigned to **`"Core Team"`** (`OUTLINE_CORE_TEAM_GROUP_ID`, or `cg_admin_group_id`) **iff** the user belongs to **at least one** active cohort (`is_active = true`).
    - Assigned to **`"Ex Core Team"`** (`OUTLINE_XCORE_TEAM_GROUP_ID`, or `xcg_admin_group_id`) **iff** the user belongs to **zero** active cohorts and **at least one** non-active cohort (`is_active = false`).
- **Stale member & shared-group cleanup:** After adding current members, the job diffs existing Outline group members (`POST /api/groups.memberships`), removes stale users from the `Core Team {year}` Outline group (`groups.remove_user`), looks up each removed Outline user's email (`users.info`), resolves their local `User`, and reconciles their `"Core Team"` / `"Ex Core Team"` group memberships (`reconcileRemovedMemberSharedGroups`).

### 4. Per-Cohort Collection Access Matrix (Own-Cohort Editor, All-Cohort View)

Each `CoreTeam` cohort receives a dedicated Outline Collection named `Core Team {year}` reconciled by `SyncCoreTeamDocsCollections` → `CreateDocsCollection` / `UpdateDocsCollection`:

- **Workspace-private baseline (`permission: null`):** Collections are created and updated with `permission = null` so workspace-wide default access is disabled. If a pre-existing Outline collection with the same name has a non-null workspace `permission`, `findOrCreateCollection()` explicitly locks it down to `permission = null`.
- **Collection group permissions (`POST /api/collections.add_group`):**

| Outline Group                                        | Target Collection(s)            | Outline Access                                                | API Permission                                 |
| ---------------------------------------------------- | ------------------------------- | ------------------------------------------------------------- | ---------------------------------------------- |
| **`Core Team {year}`** (`integration.docs_group_id`) | **Only** own `Core Team {year}` | **Editor**                                                    | `OutlinePermission::READ_WRITE` (`read_write`) |
| **`"Core Team"`** (`OUTLINE_CORE_TEAM_GROUP_ID`)     | **All** `Core Team 20xx`        | **View**                                                      | `OutlinePermission::READ` (`read`)             |
| **`"Ex Core Team"`** (`OUTLINE_XCORE_TEAM_GROUP_ID`) | **All** `Core Team 20xx`        | **View**                                                      | `OutlinePermission::READ` (`read`)             |
| Other `Core Team 20yy` groups (`20yy ≠ {year}`)      | `Core Team {year}`              | _No direct grant (View via `"Core Team"` / `"Ex Core Team"`)_ | —                                              |

- **Non-destructive collection retention on cohort deletion:**
    - Deleting a `CoreTeam` (and its local `Group` / `GroupIntegration` record) may clean up the cohort's Outline **Group** (`docs_group_id` via `groups.delete`, alongside reconciling removed members' `"Core Team"` / `"Ex Core Team"` memberships), **but must never delete the Outline Collection (`docs_collection_id`)**.
    - Retaining the collection prevents accidental destruction of cohort documents; `"Core Team"` and `"Ex Core Team"` retain **View** (`read`) access, and if the same `CoreTeam` year is later recreated, `findOrCreateCollection()` automatically re-links the preserved `Core Team {year}` collection by name and restores **Editor** (`read_write`) access to the new `Core Team {year}` group.

### 5. Scheduled Watermark Orchestration

- Outline synchronization runs asynchronously on the hourly schedule (`SyncCoreTeamDocsGroups`, `SyncXCoreTeamDocsGroups`, `SyncCoreTeamDocsCollections`), comparing `docs_group_last_synced_at` and `docs_collection_last_synced_at` against **both** `groups.updated_at` and `core_teams.updated_at` (`null` → `CreateDocs*`, `< updated_at` → `UpdateDocs*`).

## Consequences

- **Positive:**
    - `groups` stays focused on RBAC and central SSO while `group_integrations` provides a clean, vendor-agnostic (`docs_*`) extension point if Outline is ever replaced or when additional external tools are integrated with Infinity groups.
    - Core Team onboarding, cohort handovers (`is_active` transitions), and cross-year **View** / own-cohort **Editor** access in Outline are fully automated.
    - Multi-year Core Team members seamlessly retain active `Core Team` umbrella status when prior cohorts are archived to `Ex Core Team`, and deleted remote groups/collections self-heal on subsequent sync runs.
- **Invariants to Maintain:**
    - **Pipeline ordering:** `SyncCoreTeamMembersGroup` must update local `Group('Core Team {year}')` memberships before `Sync*CoreTeamDocsGroups` runs, and `Create/UpdateDocsGroup` must populate `docs_group_id` before `SyncCoreTeamDocsCollections` dispatches collection jobs.
    - **Safe membership pagination on removal:** Diffing `groups.memberships` must collect stale member IDs cleanly without skipping entries due to offset shifts during in-loop deletions.
    - **Collection permission precedence, pruning & retention:** Reconciling `collections.group_memberships` must never overwrite `integration.docs_group_id`'s `read_write` grant with `read` (if `docs_group_id` overlaps `$readOnlyGroupIds`), should remove stale group memberships no longer in the desired set, and cohort deletion flows must never invoke `OutlineService::deleteCollection()`.
    - **Distinct, bounded job locks:** Group jobs (`Create/UpdateDocsGroup`) and collection jobs (`Create/UpdateDocsCollection`) must use distinct uniqueness/overlap lock keys with bounded lock expiration so group sync and collection sync cannot block or drop one another.
