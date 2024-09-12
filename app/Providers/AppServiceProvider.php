<?php

namespace App\Providers;

use App\Repositories\PsrCacheRepository;
use App\Repositories\PsrCacheRepositoryImpl;
use App\Repositories\StorageFacade;
use App\Repositories\StorageFacadeImpl;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use SocialiteProviders\Authentik\Provider as AuthentikProvider;
use SocialiteProviders\Manager\SocialiteWasCalled;

class AppServiceProvider extends ServiceProvider
{
    /**
     * All of the container singletons that should be registered.
     *
     * @var array
     */
    public $singletons = [
        PsrCacheRepository::class => PsrCacheRepositoryImpl::class,
        StorageFacade::class => StorageFacadeImpl::class,
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
        Event::listen(function (SocialiteWasCalled $event) {
            $event->extendSocialite('authentik', AuthentikProvider::class);
        });
    }
}
