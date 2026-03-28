<?php

use App\Http\Middleware\ForceJsonResponse;
use App\Jobs\SyncAchievementLeaderboards;
use App\Jobs\SyncCGAdminMembersGroup;
use App\Jobs\SyncCGAdminSsoGroups;
use App\Jobs\SyncCoreTeamMembersGroup;
use App\Jobs\SyncCoreTeamSsoGroups;
use App\Jobs\SyncSsoUsers;
use App\Jobs\SyncXCGAdminSsoGroups;
use App\Jobs\SyncXCoreTeamSsoGroups;
use App\Utils\JsendFormatter;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\InvokeDeferredCallbacks;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance;
use Illuminate\Foundation\Http\Middleware\TrimStrings;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Middleware\TrustProxies;
use Illuminate\Http\Middleware\ValidatePathEncoding;
use Illuminate\Http\Middleware\ValidatePostSize;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Support\Arr;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        api: __DIR__.'/../routes/api.php',
        apiPrefix: '',
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->use([
            ValidatePathEncoding::class,
            InvokeDeferredCallbacks::class,
            // \Illuminate\Http\Middleware\TrustHosts::class,
            TrustProxies::class,
            HandleCors::class,
            PreventRequestsDuringMaintenance::class,
            ValidatePostSize::class,
            TrimStrings::class,
            ConvertEmptyStringsToNull::class,
        ]);

        $middleware->group('api', [
            ForceJsonResponse::class,
            'throttle:api',
            SubstituteBindings::class,
        ]);

        $middleware->group('web', [
            SubstituteBindings::class,
        ]);

        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR
                | Request::HEADER_X_FORWARDED_HOST
                | Request::HEADER_X_FORWARDED_PROTO
        );
    })
    ->withSchedule(function (Schedule $schedule) {
        $schedule->job(new SyncAchievementLeaderboards(Date::now()->year))->daily();
        $schedule->job(new SyncCoreTeamMembersGroup)->daily();
        $schedule->job(new SyncCGAdminMembersGroup)->daily();
        $schedule->job(new SyncSsoUsers)->daily();
        $schedule->job(new SyncCoreTeamSsoGroups)->daily();
        $schedule->job(new SyncCGAdminSsoGroups)->daily();
        $schedule->job(new SyncXCoreTeamSsoGroups)->daily();
        $schedule->job(new SyncXCGAdminSsoGroups)->daily();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (AuthenticationException $e) {
            return response()->json(
                JsendFormatter::fail($e->getMessage() ?: 'Unauthenticated.'),
                401,
            );
        });

        $exceptions->render(function (ValidationException $e) {
            return response()->json(
                JsendFormatter::fail(
                    $e->getMessage() ?: 'The given data was invalid.',
                    ['details' => $e->errors()],
                ),
                $e->status ?? 422,
            );
        });

        $exceptions->render(function (AccessDeniedHttpException $e) {
            return response()->json(
                JsendFormatter::fail(
                    $e->getMessage() ?: 'Forbidden.',
                    config('app.debug') ? [
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => collect($e->getTrace())->map(function ($trace) {
                            return Arr::except($trace, ['args']);
                        })->all(),
                    ] : null,
                ),
                $e->getStatusCode(),
                $e->getHeaders() ?: [],
            );
        });

        $exceptions->render(function (HttpException $e) {
            if ($e->getStatusCode() < 500) {
                return response()->json(
                    JsendFormatter::fail(
                        $e->getMessage(),
                        config('app.debug') ? [
                            'exception' => get_class($e),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                            'trace' => collect($e->getTrace())->map(function ($trace) {
                                return Arr::except($trace, ['args']);
                            })->all(),
                        ] : null,
                    ),
                    $e->getStatusCode(),
                    $e->getHeaders() ?: [],
                );
            } else {
                return response()->json(
                    JsendFormatter::error(
                        $e->getMessage(),
                        $e->getCode() ?: null,
                        config('app.debug') ? [
                            'exception' => get_class($e),
                            'file' => $e->getFile(),
                            'line' => $e->getLine(),
                            'trace' => collect($e->getTrace())->map(function ($trace) {
                                return Arr::except($trace, ['args']);
                            })->all(),
                        ] : null,
                    ),
                    $e->getStatusCode() ?? 500,
                    $e->getHeaders() ?: [],
                );
            }
        });

        $exceptions->render(function (Throwable $e) {
            return response()->json(
                JsendFormatter::error(
                    config('app.debug') ? $e->getMessage() : 'Internal server error.',
                    $e->getCode() ?: null,
                    config('app.debug') ? [
                        'exception' => get_class($e),
                        'file' => $e->getFile(),
                        'line' => $e->getLine(),
                        'trace' => collect($e->getTrace())->map(function ($trace) {
                            return Arr::except($trace, ['args']);
                        })->all(),
                    ] : null,
                ),
                500,
            );
        });
    })
    ->create();
