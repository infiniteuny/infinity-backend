<?php

namespace App\Providers;

use App\Guards\DummyGuard;
use App\Guards\OidcHeadersGuard;
use App\Guards\OidcTokenGuard;
use App\Repositories\PsrCacheRepository;
use App\Repositories\PsrCacheRepositoryImpl;
use App\Repositories\StorageRepository;
use App\Repositories\StorageRepositoryImpl;
use App\Services\OidcService;
use App\Services\OidcServiceImpl;
use App\Services\SsoService;
use App\Services\SsoServiceImpl;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * All of the container singletons that should be registered.
     *
     * @var array
     */
    public $singletons = [
        PsrCacheRepository::class => PsrCacheRepositoryImpl::class,
        StorageRepository::class => StorageRepositoryImpl::class,
        OidcService::class => OidcServiceImpl::class,
        SsoService::class => SsoServiceImpl::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Storage::disk('local')->buildTemporaryUrlsUsing(function ($path, $expiration, $options) {
            return URL::to(
                URL::temporarySignedRoute(
                    'blobs.show',
                    $expiration,
                    array_merge($options, ['blob' => $path]),
                    false,
                ),
            );
        });
        Auth::extend('oidc_token', function ($app, $name, array $config) {
            return new OidcTokenGuard(
                Auth::createUserProvider($config['provider']),
                $app->make(OidcService::class),
                $app->make('request'),
            );
        });
        Auth::extend('oidc_headers', function ($app, $name, array $config) {
            return new OidcHeadersGuard(
                Auth::createUserProvider($config['provider']),
                $app->make(OidcService::class),
                $app->make('request'),
            );
        });
        Auth::extend('dummy', function ($app, $name, array $config) {
            return new DummyGuard(
                Auth::createUserProvider($config['provider']),
                $app->make('request'),
                config('auth.dummy_user_id')
            );
        });
        Http::macro('authentik', function () {
            return Http::withToken(config('services.authentik.token'))
                ->baseUrl(config('services.authentik.base_url'));
        });
        RateLimiter::for('api', function (Request $request) {
            if (! config('app.rate_limiter.enabled')) {
                return Limit::none();
            } else {
                $user = Auth::guard('api_token')->user() ?: Auth::guard('api_token')->user();

                return Limit::perSecond(100)->by($user?->id ?: $request->ip());
            }
        });
    }
}
