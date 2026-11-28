<?php

namespace Tests\Unit\Providers;

use App\Providers\AppServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class AppServiceProviderTest extends TestCase
{
    public function test_forces_https_scheme_for_https_app_url_and_sets_http_macro_timeouts(): void
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
}
