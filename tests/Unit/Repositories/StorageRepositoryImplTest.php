<?php

namespace Tests\Unit\Repositories;

use App\Enums\StorageVisibility;
use App\Facades\Storage as BlobStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage as LaravelStorage;
use InvalidArgumentException;
use Tests\TestCase;

class StorageRepositoryImplTest extends TestCase
{
    public function test_stores_deletes_idempotently_and_validates_manifests(): void
    {
        LaravelStorage::fake('local');

        $manifest = BlobStorage::store(
            UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf'),
            'docs',
            StorageVisibility::PRIVATE,
        );

        $this->assertTrue(BlobStorage::delete($manifest));
        $this->assertTrue(BlobStorage::delete($manifest));
        $this->assertTrue(BlobStorage::delete(''));

        $this->expectException(InvalidArgumentException::class);
        BlobStorage::url('{"disk":"local"}');
    }
}
