<?php

namespace App\Providers;

use App\Guards\OidcGuard;
use App\Repositories\PsrCacheRepository;
use App\Repositories\PsrCacheRepositoryImpl;
use App\Repositories\StorageRepository;
use App\Repositories\StorageRepositoryImpl;
use App\Services\OidcService;
use App\Services\OidcServiceImpl;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        Auth::extend('oidc', function ($app, $name, array $config) {
            return new OidcGuard(
                Auth::createUserProvider($config['provider']),
                $app->make(OidcService::class),
                $app->make('request'),
            );
        });
        RateLimiter::for('api', function (Request $request) {
            return Limit::perSecond(100)->by($request->user()?->id ?: $request->ip());
        });
    }
}
