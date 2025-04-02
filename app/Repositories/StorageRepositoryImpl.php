<?php

namespace App\Repositories;

use App\Enums\StorageVisibility;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
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
        ?string $disk = null,
        ?string $name = null,
    ): string {
        if (is_null($name)) {
            $name = Uuid::v7().'.'.$file->extension();
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
        $params = json_decode($manifest);
        $disk = $params->disk;
        $visibility = $params->visibility;
        $path = $params->path;
        $name = $params->name;

        if ($visibility === StorageVisibility::PRIVATE->value) {
            // For private files, we need to generate a temporary URL
            // with a specific expiration time
            $expiration = Carbon::now()->addHours(6);

            return Storage::disk($disk)->temporaryUrl($path.'/'.$name, $expiration);
        } else {
            return Storage::disk($disk)->url($path.'/'.$name);
        }
    }

    public function delete(string $manifest): bool
    {
        $params = json_decode($manifest);
        $disk = $params->disk;
        $path = $params->path;
        $name = $params->name;

        if (Storage::disk($disk)->exists($path.'/'.$name)) {
            return Storage::disk($disk)->delete($path.'/'.$name);
        } else {
            throw new NotFoundHttpException('File not found.');
        }
    }
}
