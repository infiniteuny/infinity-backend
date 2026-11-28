<?php

namespace App\Repositories;

use App\Enums\StorageVisibility;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use Symfony\Component\Uid\Uuid;

class StorageRepositoryImpl implements StorageRepository
{
    /**
     * @throws Exception
     */
    public function store(
        UploadedFile $file,
        string $path,
        StorageVisibility $visibility = StorageVisibility::PRIVATE,
        ?string $name = null,
        ?string $disk = null,
    ): string {
        if (is_null($name)) {
            $extension = trim((string) $file->extension(), '.');
            $name = $extension !== '' ? Uuid::v7().'.'.$extension : (string) Uuid::v7();
        } else {
            $name = Str::ascii($name);
        }

        if (is_null($disk)) {
            $disk = config('filesystems.default');
        }

        $visibility = $visibility->value;
        $path = trim($visibility.'/'.$path, '/');

        $result = Storage::disk($disk)->putFileAs($path, $file, $name, $visibility);

        if ($result) {
            return json_encode([
                'disk' => $disk,
                'visibility' => $visibility,
                'path' => $path,
                'name' => $name,
            ]);
        } else {
            throw new Exception('Failed to store file');
        }
    }

    public function url(string $manifest): string
    {
        $params = $this->parseManifest($manifest, requireVisibility: true);
        $disk = $params['disk'];
        $visibility = $params['visibility'];
        $filePath = trim($params['path'].'/'.$params['name'], '/');

        if ($visibility === StorageVisibility::PRIVATE->value) {
            $expiration = Carbon::now()->addHours(6);

            return Storage::disk($disk)->temporaryUrl($filePath, $expiration);
        }

        return Storage::disk($disk)->url($filePath);
    }

    public function delete(string $manifest): bool
    {
        if (trim($manifest) === '') {
            return true;
        }

        $params = $this->parseManifest($manifest, requireVisibility: false);
        $disk = $params['disk'];
        $filePath = trim($params['path'].'/'.$params['name'], '/');

        if (! Storage::disk($disk)->exists($filePath)) {
            return true;
        }

        return Storage::disk($disk)->delete($filePath);
    }

    /**
     * @return array{disk: string, path: string, name: string, visibility?: string}
     */
    private function parseManifest(string $manifest, bool $requireVisibility): array
    {
        $decoded = json_decode($manifest, true);

        if (
            ! is_array($decoded)
            || ! is_string($decoded['disk'] ?? null) || $decoded['disk'] === ''
            || ! is_string($decoded['path'] ?? null)
            || ! is_string($decoded['name'] ?? null) || $decoded['name'] === ''
            || ($requireVisibility && (! is_string($decoded['visibility'] ?? null) || $decoded['visibility'] === ''))
        ) {
            throw new InvalidArgumentException('Invalid storage manifest.');
        }

        return $decoded;
    }
}
