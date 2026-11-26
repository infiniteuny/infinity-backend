# ADR-0004: Team Permissions, Leadership Transfer, Membership Rules, and Automated Individual Teams

- **Status:** Accepted
- **Date:** 2026-11-27

## Context

Teams (`App\Models\Team`) and team memberships (`App\Models\TeamMember`) underpin competition participation, `Achievement` submissions, `FundApplication` requests, and `SyncAchievementLeaderboards` point aggregation.

Because multiple actors interact with team resources — administrators (`*-...`), team leaders (`*-own-...`), and non-leader team members (`*-own-...`) — alongside system-managed **Individual Teams** (`config('app.individual_team_type_id')`), clear invariants are required around:

1. Which actions require team leadership versus general team membership.
2. How `leader_id` relates to the `team_members` pivot table on creation and leadership transfer.
3. What a non-leader team member is allowed to do with their own `TeamMember` record (leaving/rejecting membership vs. swapping `user_id`).
4. How Individual Teams are provisioned and protected from manual API mutation.
5. How post-approval edits (`Achievement` / `FundApplication`) and foreign-key ownership (`OwnedEntity`) behave for non-approvers and `*-own-*` roles.

## Decision

### 1. Leader vs. Member Permission Matrix (`*-own-*` vs. Global)

| Resource & Action                            | Global Permission                                        | Team Leader (`*-own-*`)                                                            | Non-Leader Member (`*-own-*`)                                |
| -------------------------------------------- | -------------------------------------------------------- | ---------------------------------------------------------------------------------- | ------------------------------------------------------------ |
| **Team — List / View**                       | `read-team`                                              | Allowed                                                                            | Allowed                                                      |
| **Team — Create**                            | `create-team` _(any `leader_id`)_                        | Allowed _(non-Individual only; `leader_id` must be self)_                          | —                                                            |
| **Team — Update**                            | `update-team` _(non-Individual only)_                    | Allowed _(non-Individual only; may transfer `leader_id` to another user)_          | Denied (`403`)                                               |
| **Team — Delete**                            | `delete-team` _(non-Individual only)_                    | Allowed _(non-Individual only)_                                                    | Denied (`403`)                                               |
| **TeamMember — List / View**                 | `read-team-member`                                       | Allowed                                                                            | Allowed                                                      |
| **TeamMember — Create**                      | `create-team-member` _(non-Individual only)_             | Allowed _(non-Individual only)_                                                    | Denied (`403`)                                               |
| **TeamMember — Update**                      | `update-team-member` _(non-Individual; non-leader rows)_ | Allowed _(non-Individual; non-leader rows)_                                        | Allowed on own row _(cannot swap `user_id` to another user)_ |
| **TeamMember — Delete**                      | `delete-team-member` _(non-Individual; non-leader rows)_ | Allowed _(non-Individual; can remove non-leader members)_                          | Allowed on own row _(can leave / reject team membership)_    |
| **Achievement — Create / Update**            | `create-achievement` / `update-achievement`              | Allowed _(own teams only; resets to `PENDING` without `approve-achievement`)_      | Allowed _(same as leader)_                                   |
| **Achievement — Delete**                     | `delete-achievement`                                     | Allowed                                                                            | Denied (`403`)                                               |
| **FundApplication — View / Create / Update** | `*-fund-application`                                     | Allowed _(own teams only; resets to `PENDING` without `approve-fund-application`)_ | Allowed _(same as leader)_                                   |
| **FundApplication — Delete**                 | `delete-fund-application`                                | Allowed                                                                            | Denied (`403`)                                               |

### 2. Automated Individual Teams Are Strictly Read-Only via API

- Individual Teams (`teams.team_type_id === config('app.individual_team_type_id')`) and their single-user `team_members` records are created and reconciled **exclusively** by the scheduled `App\Jobs\SyncIndividualTeams` background job for registered members (`users.is_member = true`).
- Individual Teams **cannot** be manually created, updated (neither renamed, reassigned, nor converted to/from another `team_type_id`), or deleted through the API — even by users holding global `*-team` permissions.
- Memberships (`TeamMember`) belonging to an Individual Team **cannot** be manually created, updated, or deleted through the API — even by users holding global `*-team-member` permissions.

### 3. Leader-Is-Member Invariant & Leadership Transfer

- A team's leader (`teams.leader_id`) **must always** also exist as a member row in `team_members`.
- On team creation (`POST /v1/teams`), `leader_id` is restricted to the authenticated creator when lacking `create-team` (`OwnedEntity(User::class, $user->id)`), and `leader_id` is atomically attached to `team_members`.
- On team update (`PUT`/`PATCH /v1/teams/{team}`), the current team leader (`update-own-team`) or an administrator (`update-team`) **may transfer `leader_id` to another user**, and updating `leader_id` must atomically attach the new leader to `team_members` (`syncWithoutDetaching([$team->leader_id])`) if they are not already a member.
- While a user is the team's `leader_id`, their corresponding `team_members` record cannot be deleted or have its `user_id` modified until leadership is transferred to another user.

### 4. Team Member Self-Management & Anti-Swap Rule

- A non-leader team member with `delete-own-team-member` can delete (`DELETE /v1/team-members/{team_member}`) their own membership row in order to leave or reject membership in a team.
- A non-leader team member with `update-own-team-member` may access `update` on their own membership row (for any self-editable membership metadata), **but cannot swap `user_id` to another user** (`OwnedEntity(User::class, $user->id)` applies when the actor lacks `update-team-member` and is not the team's leader). Replacing one user with another user on a team requires either the team leader (`$team->leader_id === $user->id`) or `update-team-member`.

### 5. Associated Achievements & Fund Applications

- Both leaders and non-leader members of a team may submit and edit `Achievement` and `FundApplication` records for teams they belong to (`OwnedEntity(Team::class, $user->id)` when lacking `create-*` / `update-*`), while only the team leader (or global `delete-*`) may delete them.
- Any edit to an `Achievement` or `FundApplication` by a user lacking `approve-achievement` / `approve-fund-application` resets `status` to `'PENDING'` so approved (`ACCEPTED`) records cannot be modified without re-approval.

## Consequences

- **Positive:**
    - `SyncAchievementLeaderboards` can reliably join `teams -> team_members -> users` knowing every leader (and every Individual Team owner) is guaranteed to have a `team_members` row.
    - Individual competition attribution cannot be spoofed or corrupted by manual API calls adding extra members or mutating Individual Teams.
    - Team leaders can hand over leadership without admin intervention while preventing non-leader members from silently swapping arbitrary users into a team.
- **Invariants to Maintain:**
    - Any code path mutating `teams.leader_id` must run inside a transaction that ensures the new `leader_id` is attached to `team_members`.
    - `TeamPolicy`, `TeamMemberPolicy`, and `Team` / `TeamMember` FormRequests must uniformly enforce the `config('app.individual_team_type_id')` immutability boundary and the non-leader anti-swap constraint.
