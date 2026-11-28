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

    public function test_stores_file_without_extension_without_trailing_dot(): void
    {
        LaravelStorage::fake('local');

        $tempPath = tempnam(sys_get_temp_dir(), 'blob_no_ext_');
        file_put_contents($tempPath, '');

        try {
            $file = new class($tempPath, 'README', 'application/octet-stream', null, true) extends UploadedFile
            {
                public function extension(): string
                {
                    return '';
                }
            };

            $manifest = BlobStorage::store(
                $file,
                'docs',
                StorageVisibility::PRIVATE,
                null,
                'local',
            );

            $decoded = json_decode($manifest, true);
            $this->assertIsArray($decoded);
            $this->assertStringEndsNotWith('.', $decoded['name']);
            $this->assertStringNotContainsString('.', $decoded['name']);
            LaravelStorage::disk('local')->assertExists($decoded['path'].'/'.$decoded['name']);
        } finally {
            @unlink($tempPath);
        }
    }
}
