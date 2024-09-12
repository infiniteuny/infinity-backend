<?php

namespace App\Repositories;

use Illuminate\Http\UploadedFile;

interface StorageRepository
{
    public function store(
        UploadedFile $file,
        string $path,
        ?string $name = null,
        string $disk = 'local',
    ): string;

    public function get(string $manifest): string;

    public function delete(string $manifest): bool;
}
