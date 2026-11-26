<?php

namespace App\Repositories;

use App\Enums\StorageVisibility;
use Illuminate\Http\UploadedFile;

interface StorageRepository
{
    public function store(
        UploadedFile $file,
        string $path,
        StorageVisibility $visibility = StorageVisibility::PRIVATE,
        ?string $name = null,
        ?string $disk = null,
    ): string;

    public function url(string $manifest): string;

    public function delete(string $manifest): bool;
}
