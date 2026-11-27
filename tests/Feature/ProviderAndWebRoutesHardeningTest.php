<?php

namespace Tests\Feature;

use App\Providers\AppServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Tests\TestCase;

class ProviderAndWebRoutesHardeningTest extends TestCase
{
    public function test_https_app_url_forces_https_scheme_even_on_http_console_request(): void
    {
        $originalUrl = config('app.url');

        try {
            URL::setRequest(Request::create('http://localhost'));
            config(['app.url' => 'https://api.infiniteuny.id']);

            (new AppServiceProvider($this->app))->boot();

            $signedBlobUrl = Storage::disk('local')->temporaryUrl(
                'fund-applications/proposals/doc.pdf',
                now()->addMinutes(5),
            );

            $this->assertStringStartsWith('https://api.infiniteuny.id/static/private/fund-applications/proposals/doc.pdf?', $signedBlobUrl);
        } finally {
            config(['app.url' => $originalUrl]);
            URL::forceScheme(null);
            (new AppServiceProvider($this->app))->boot();
        }
    }

    public function test_authentik_and_outline_http_macros_configure_explicit_timeouts_and_handle_null_tokens(): void
    {
        config([
            'services.authentik.token' => null,
            'services.authentik.base_url' => 'https://auth.infiniteuny.id',
            'services.outline.token' => null,
            'services.outline.base_url' => 'https://docs.infiniteuny.id',
        ]);

        $authentikOptions = Http::authentik()->getOptions();
        $outlineOptions = Http::outline()->getOptions();

        $this->assertSame(10, $authentikOptions['connect_timeout']);
        $this->assertSame(30, $authentikOptions['timeout']);
        $this->assertSame(10, $outlineOptions['connect_timeout']);
        $this->assertSame(30, $outlineOptions['timeout']);
    }

    public function test_scribe_web_routes_return_404_when_artifacts_missing_and_serve_files_when_present(): void
    {
        $scribeDir = storage_path('app/scribe');
        $collectionPath = $scribeDir.'/collection.json';
        $openapiPath = $scribeDir.'/openapi.yaml';
        $viewPath = resource_path('views/scribe/index.blade.php');

        $hadCollection = is_file($collectionPath) ? file_get_contents($collectionPath) : null;
        $hadOpenapi = is_file($openapiPath) ? file_get_contents($openapiPath) : null;
        $hadView = is_file($viewPath) ? file_get_contents($viewPath) : null;

        try {
            File::delete([$collectionPath, $openapiPath, $viewPath]);
            View::getFinder()->flush();

            $this->get('/')->assertNotFound();
            $this->get('/collection.json')->assertNotFound();
            $this->get('/openapi.yaml')->assertNotFound();

            File::ensureDirectoryExists($scribeDir);
            File::ensureDirectoryExists(dirname($viewPath));
            File::put($collectionPath, '{"info":{"name":"Infinity API"}}');
            File::put($openapiPath, "openapi: 3.0.3\ninfo:\n  title: Infinity API\n");
            File::put($viewPath, '<!DOCTYPE html><html><body>Docs</body></html>');
            View::getFinder()->flush();

            $this->get('/')->assertOk()->assertSee('Docs');

            $collectionResponse = $this->get('/collection.json');
            $collectionResponse->assertOk();
            $this->assertStringContainsString('application/json', (string) $collectionResponse->headers->get('Content-Type'));

            $openapiResponse = $this->get('/openapi.yaml');
            $openapiResponse->assertOk();
            $this->assertStringContainsString('yaml', (string) $openapiResponse->headers->get('Content-Type'));
        } finally {
            if ($hadCollection !== null) {
                File::ensureDirectoryExists($scribeDir);
                File::put($collectionPath, $hadCollection);
            } else {
                File::delete($collectionPath);
            }

            if ($hadOpenapi !== null) {
                File::ensureDirectoryExists($scribeDir);
                File::put($openapiPath, $hadOpenapi);
            } else {
                File::delete($openapiPath);
            }

            if ($hadView !== null) {
                File::ensureDirectoryExists(dirname($viewPath));
                File::put($viewPath, $hadView);
            } else {
                File::delete($viewPath);
                if (is_dir(dirname($viewPath)) && count(File::allFiles(dirname($viewPath))) === 0) {
                    File::deleteDirectory(dirname($viewPath));
                }
            }

            View::getFinder()->flush();
        }
    }
}
