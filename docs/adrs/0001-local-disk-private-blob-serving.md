# ADR-0001: Local-Disk Private Blob Serving & Default Private Storage Visibility

- **Status:** Accepted
- **Date:** 2026-11-27

## Context

Infinity stores uploaded files (photos, logos, achievement certificates, funding proposals, and letters of acceptance) through `App\Repositories\StorageRepository` (`StorageRepositoryImpl`) and serializes URLs dynamically via the `App\Casts\Blob` Eloquent cast.

Deployments may use either:

1. **`s3` disk** (S3-compatible object storage in production), which natively generates time-limited presigned URLs via `Storage::disk('s3')->temporaryUrl(...)`.
2. **`local` disk** (`storage/app/local`), which supports public files via the `public/static/public -> storage/app/local/public` symlink, while private files (`storage/app/local/private`) must never be directly web-accessible without a valid signature.

Without explicit documentation, two implementation choices in `BlobController`, `BlobRequest`, and `StorageRepository` can look counterintuitive during code review:

- Why `BlobController` hardcodes `Storage::disk('local')` instead of reading a dynamic disk.
- Why `BlobRequest` is attached to a `GET` endpoint (`GET /static/private/{blob}`).

## Decision

1. **`BlobController` is exclusively for `local` disk private blobs:**
    - `AppServiceProvider` registers `Storage::disk('local')->buildTemporaryUrlsUsing(...)` to generate signed relative URLs pointing to `route('blobs.show')` (`GET /static/private/{blob}`).
    - `BlobController` intentionally serves files only from `Storage::disk('local')`. When blobs are stored on the `s3` disk, `StorageRepositoryImpl::url()` delegates directly to Flysystem S3 presigned URLs, and `BlobController` is never invoked.
2. **`BlobRequest` validates optional `GET` query parameters:**
    - `BlobRequest` validates query string parameters on `GET /static/private/{blob}` — specifically `force_download` (boolean toggle between `download()` attachment vs `serve()` inline response) and `name` (custom download/display filename).
3. **Private-by-default blob storage:**
    - `StorageRepository::store()` defaults `$visibility` to `StorageVisibility::PRIVATE` (generating 6-hour signed URLs on read) so sensitive uploads (such as `FundApplication` proposals and LoAs) cannot be accidentally exposed publicly.
    - Publicly cacheable assets (such as community group logos, persona logos, core team photos, testimonials, and project galleries) explicitly pass `StorageVisibility::PUBLIC`.

## Consequences

- **Positive:** Uniform `Blob` cast and `StorageRepository` abstraction across `local` and `s3` disks; sensitive documents are private by default; `s3` reads offload bandwidth directly to object storage without proxying bytes through PHP workers.
- **Trade-offs & Invariants:**
    - `BlobController` must treat `$request->validated(...)` as optional query parameters (defaulting `name` to `null` and `force_download` to `false`).
    - Any signature check on `BlobController` must account for optional presentation query parameters (`force_download`, `name`) if callers append them to pre-generated signed URLs.
    - Response security headers (`Content-Security-Policy: default-src 'none'; style-src 'unsafe-inline'; sandbox` and `Cache-Control: no-store`) are enforced on `BlobController` responses to prevent stored XSS when rendering user-uploaded files inline from the API origin.
