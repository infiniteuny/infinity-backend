<?php

namespace App\Providers;

use App\Repositories\OidcFacade;
use App\Repositories\OidcFacadeImpl;
use App\Repositories\PsrCacheRepository;
use App\Repositories\PsrCacheRepositoryImpl;
use App\Repositories\StorageRepository;
use App\Repositories\StorageRepositoryImpl;
use App\Guards\OidcGuard;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
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
        OidcFacade::class => OidcFacadeImpl::class,
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
        RateLimiter::for('api', function (Request $request) {
            return Limit::perSecond(100)->by($request->user()?->id ?: $request->ip());
        });
        Auth::extend('oidc', function ($app, $name, array $config) {
            return new OidcGuard(
                Auth::createUserProvider($config['provider']),
                $app->make(OidcFacade::class),
                $app->make('request'),
            );
        });
    }
}
