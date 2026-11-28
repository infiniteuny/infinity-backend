<?php

namespace Tests\Unit\Http\Controllers;

use App\Enums\StorageVisibility;
use App\Facades\Storage as BlobStorage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage as LaravelStorage;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class BlobControllerTest extends TestCase
{
    public function test_serves_files_preserves_headers_supports_force_download_and_returns_404_on_directories_or_traversal(): void
    {
        LaravelStorage::fake('local');

        $manifest = BlobStorage::store(
            UploadedFile::fake()->create('proposal.pdf', 12, 'application/pdf'),
            'fund-applications/proposals',
            StorageVisibility::PRIVATE,
        );
        $params = json_decode($manifest, true);
        $blobPath = $params['path'].'/'.$params['name'];

        $signedUrl = URL::temporarySignedRoute('blobs.show', now()->addMinutes(10), ['blob' => $blobPath], false);
        $inlineResponse = $this->get($signedUrl.'&force_download=false');
        $inlineResponse->assertOk();
        $this->assertStringContainsString('inline', (string) $inlineResponse->headers->get('Content-Disposition'));
        $this->assertSame(
            "default-src 'none'; style-src 'unsafe-inline'; sandbox",
            $inlineResponse->headers->get('Content-Security-Policy'),
        );

        $downloadResponse = $this->get($signedUrl.'&force_download=true');
        $downloadResponse->assertOk();
        $this->assertStringContainsString('attachment', (string) $downloadResponse->headers->get('Content-Disposition'));

        LaravelStorage::disk('local')->serveUsing(function () {
            return new Response('PDF-BYTES', 200, [
                'Content-Type' => 'application/pdf',
                'X-Custom-Served' => '1',
            ]);
        });
        $customServed = $this->get($signedUrl);
        $customServed->assertOk();
        $this->assertSame('application/pdf', $customServed->headers->get('Content-Type'));
        $this->assertSame('1', $customServed->headers->get('X-Custom-Served'));
        $this->assertSame(
            "default-src 'none'; style-src 'unsafe-inline'; sandbox",
            $customServed->headers->get('Content-Security-Policy'),
        );

        $dirUrl = URL::temporarySignedRoute('blobs.show', now()->addMinutes(10), ['blob' => $params['path']], false);
        $this->getJson($dirUrl)->assertNotFound();

        $traversalUrl = URL::temporarySignedRoute('blobs.show', now()->addMinutes(10), ['blob' => 'private/../.env'], false);
        $this->getJson($traversalUrl)->assertNotFound();
    }
}
