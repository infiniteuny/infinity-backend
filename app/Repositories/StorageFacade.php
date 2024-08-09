<?php

namespace App\Repositories;

use Illuminate\Http\UploadedFile;

interface StorageFacade
{
    public static function store(
        UploadedFile $file,
        string $path,
        ?string $name = null,
        string $disk = 'local',
    ): string;

    public static function get(string $manifest): string;

    public static function delete(string $manifest): bool;
}
