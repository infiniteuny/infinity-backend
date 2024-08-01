<?php

namespace App\Repositories;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Uid\Uuid;

class StorageFacadeImpl implements StorageFacade
{
    public static function store(
        UploadedFile $file,
        string $path,
        ?string $name = null,
        string $disk = 'local',
    ): string {
        if (is_null($name)) {
            $name = Uuid::v7().'.'.$file->extension();
        }

        $file->storeAs($path, $name, $disk);

        $manifest = json_encode([
            'disk' => $disk,
            'path' => $path,
            'name' => $name,
        ]);

        if (! $manifest) {
            throw new \Exception('Failed to store file');
        } else {
            return $manifest;
        }
    }

    public static function get(string $manifest): string
    {
        $params = json_decode($manifest);
        $disk = $params->disk;
        $path = $params->path;
        $name = $params->name;

        if (! Storage::disk($disk)->exists($path.'/'.$name)) {
            throw new NotFoundHttpException('File not found.');
        } else {
            return Storage::disk($disk)->path($path.'/'.$name);
        }
    }

    public static function delete(string $manifest): bool
    {
        $params = json_decode($manifest);
        $disk = $params->disk;
        $path = $params->path;
        $name = $params->name;

        return Storage::disk($disk)->delete($path.'/'.$name);
    }
}
