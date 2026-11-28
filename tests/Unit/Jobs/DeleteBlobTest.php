<?php

namespace Tests\Unit\Jobs;

use App\Enums\StorageVisibility;
use App\Facades\Storage as BlobStorage;
use App\Jobs\DeleteBlob;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage as LaravelStorage;
use Tests\TestCase;

class DeleteBlobTest extends TestCase
{
    public function test_deletes_stored_blob_and_is_idempotent_on_missing_or_blank_manifests(): void
    {
        LaravelStorage::fake('local');

        $manifest = BlobStorage::store(
            UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf'),
            'docs',
            StorageVisibility::PRIVATE,
        );

        (new DeleteBlob($manifest))->handle();
        (new DeleteBlob($manifest))->handle();
        (new DeleteBlob('   '))->handle();

        $decoded = json_decode($manifest, true);
        $this->assertFalse(LaravelStorage::disk('local')->exists($decoded['path'].'/'.$decoded['name']));
    }
}
