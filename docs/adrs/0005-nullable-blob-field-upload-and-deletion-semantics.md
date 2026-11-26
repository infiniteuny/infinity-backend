# ADR-0005: Nullable Blob Field Upload and Deletion-by-Null Semantics

- **Status:** Accepted
- **Date:** 2026-11-27

## Context

Some models store optional media attachments alongside required ones — such as `animation` on `App\Models\CoreTeamMember` and `App\Models\CommunityGroupAdminMember` (`'nullable', 'file'` on `store`; `'sometimes', 'nullable', 'file'` on `update`).

Clients updating a record with an optional blob field need to express three distinct intents without dedicated sub-endpoints (e.g., `DELETE .../animation`):

1. **Keep existing blob:** Omit the field from the `PATCH`/`PUT` payload.
2. **Replace blob:** Upload a new file under that field.
3. **Remove blob:** Explicitly clear the field by sending `null` (or `""` in `multipart/form-data`, which global `ConvertEmptyStringsToNull` normalizes to `null`).

Because Laravel's `$request->has('animation')` returns `true` whenever the key exists in the payload — **even when its value is `null`** — whereas `$request->hasFile('animation')` returns `true` only when a valid `UploadedFile` was uploaded, conflating the two methods either breaks `null`-based deletion (if `update()` only checks `hasFile()`) or crashes `Storage::store($request->file('animation'), ...)` with a `TypeError` when `null` is sent (if `store()` only checks `has()`).

## Decision

1. **Creation (`store`) uses `$request->hasFile('<field>')`:**
    - Optional file fields are validated as `['nullable', 'file', ...]`.
    - Controllers check `$request->hasFile('<field>')` before invoking `Storage::store(...)`. Whether the client omits the field or sends `null`/empty, storage is skipped and `null` is persisted.
2. **Update (`update`) combines `$request->has('<field>')` and `$request->hasFile('<field>')` for three-state semantics:**
    - Optional file fields are validated as `['sometimes', 'nullable', 'file', ...]`.
    - **Field omitted (`! $request->has('<field>')`):** Leave the existing blob manifest unchanged.
    - **File uploaded (`$request->has('<field>') && $request->hasFile('<field>')`):** Dispatch `DeleteBlob` for the previous manifest (if non-null), store the new file via `Storage::store(...)`, and persist the new manifest.
    - **Explicit `null` (`$request->has('<field>') && ! $request->hasFile('<field>')`):** Dispatch `DeleteBlob` for the previous manifest (if non-null) and update the attribute to `null`.

## Consequences

- **Positive:**
    - Clients can remove optional media in the same `PUT`/`PATCH` update request simply by passing `null`, with automatic background cleanup of the old blob via `DeleteBlob`.
    - Form submissions sending empty/null optional file fields on creation succeed cleanly without `TypeError` or orphaned sibling uploads.
- **Invariants to Maintain:**
    - Never pass `$request->file('<field>')` to `Storage::store(...)` on a `nullable` file attribute unless guarded by `$request->hasFile('<field>')`.
    - Required (non-nullable) file attributes (`['required', 'file', ...]` / `['sometimes', 'file', ...]`) reject `null` at validation time (`422`), whereas nullable file attributes use `null` intentionally as the deletion signal on `update`.
