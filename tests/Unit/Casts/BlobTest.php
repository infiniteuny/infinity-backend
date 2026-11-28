<?php

namespace Tests\Unit\Casts;

use App\Casts\Blob;
use App\Enums\StorageVisibility;
use App\Facades\Storage as BlobStorage;
use App\Models\Achievement;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage as LaravelStorage;
use Tests\TestCase;

class BlobTest extends TestCase
{
    public function test_returns_url_for_valid_manifest_and_null_for_empty_values(): void
    {
        LaravelStorage::fake('local');

        $manifest = BlobStorage::store(
            UploadedFile::fake()->create('cert.pdf', 10, 'application/pdf'),
            'achievements',
            StorageVisibility::PUBLIC,
            null,
            'local',
        );

        $cast = new Blob;
        $model = new Achievement;

        $url = $cast->get($model, 'image', $manifest, ['image' => $manifest]);
        $this->assertIsString($url);
        $this->assertNotSame('', $url);

        $this->assertNull($cast->get($model, 'image', null, []));
        $this->assertNull($cast->get($model, 'image', '', ['image' => '']));
        $this->assertNull($cast->get($model, 'image', '   ', ['image' => '   ']));
    }

    public function test_returns_null_instead_of_throwing_for_corrupted_manifests(): void
    {
        $cast = new Blob;
        $model = new Achievement;

        foreach (['not-json', '{"disk":"local"}', '{"disk":"","path":"a","name":"b","visibility":"public"}', '[]'] as $corrupted) {
            $this->assertNull($cast->get($model, 'image', $corrupted, ['image' => $corrupted]));

            $achievement = new Achievement;
            $achievement->setRawAttributes(['image' => $corrupted]);
            $this->assertNull($achievement->image);
        }
    }
}
