<?php

use App\Http\Middleware\ForceJsonResponse;
use App\Http\Middleware\SanitizeApiQuery;
use App\Jobs\SyncAchievementLeaderboards;
use App\Jobs\SyncActiveInactiveMemberGroup;
use App\Jobs\SyncActiveInactiveMemberSsoGroup;
use App\Jobs\SyncAdminSsoGroup;
use App\Jobs\SyncCGAdminMembersGroup;
use App\Jobs\SyncCGAdminSsoGroups;
use App\Jobs\SyncCoreTeamDocsCollections;
use App\Jobs\SyncCoreTeamDocsGroups;
use App\Jobs\SyncCoreTeamMembersGroup;
use App\Jobs\SyncCoreTeamSsoGroups;
use App\Jobs\SyncIndividualTeams;
use App\Jobs\SyncMemberGroup;
use App\Jobs\SyncMemberSsoGroup;
use App\Jobs\SyncSsoUsers;
use App\Jobs\SyncXCGAdminSsoGroups;
use App\Jobs\SyncXCoreTeamDocsGroups;
use App\Jobs\SyncXCoreTeamSsoGroups;
use App\Utils\JsendFormatter;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Database\QueryException;
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
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Date;
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
            SanitizeApiQuery::class,
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
        $schedule->job(new SyncIndividualTeams)->hourly();
        $schedule->call(function () {
            Bus::chain([
                new SyncMemberGroup,
                new SyncActiveInactiveMemberGroup,
                new SyncCoreTeamMembersGroup,
                new SyncCGAdminMembersGroup,
                new SyncSsoUsers,
                new SyncCoreTeamSsoGroups,
                new SyncCGAdminSsoGroups,
                new SyncXCoreTeamSsoGroups,
                new SyncXCGAdminSsoGroups,
                new SyncMemberSsoGroup,
                new SyncAdminSsoGroup,
                new SyncActiveInactiveMemberSsoGroup,
                new SyncCoreTeamDocsGroups,
                new SyncXCoreTeamDocsGroups,
                new SyncCoreTeamDocsCollections,
            ])->dispatch();
        })->name('sync-groups-and-integrations')->hourly();
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

        $exceptions->render(function (UnexpectedValueException $e, Request $request) {
            if ($request->isMethodSafe() && $request->query->has('cursor')) {
                return response()->json(
                    JsendFormatter::fail('Invalid pagination cursor.'),
                    400,
                );
            }
        });

        $exceptions->render(function (InvalidArgumentException $e, Request $request) {
            if (
                $request->isMethodSafe()
                && $request->query->has('cursor')
                && str_contains($e->getMessage(), 'Illegal operator and value combination')
            ) {
                return response()->json(
                    JsendFormatter::fail('Invalid pagination cursor.'),
                    400,
                );
            }
        });

        $exceptions->render(function (QueryException $e, Request $request) {
            $sqlState = (string) ($e->errorInfo[0] ?? $e->getCode());
            $driverCode = $e->errorInfo[1] ?? null;
            $driverMessage = (string) ($e->errorInfo[2] ?? $e->getMessage());

            if (
                $sqlState === '23503'
                || ($sqlState === '23000' && (
                    in_array($driverCode, [1451, 1452], true)
                    || ($driverCode === 19 && str_contains(strtolower($driverMessage), 'foreign key'))
                ))
            ) {
                return response()->json(
                    JsendFormatter::fail(
                        'Operation cannot be completed due to a foreign key constraint, which usually means that the record is still referenced by other records.',
                    ),
                    409,
                );
            }

            if (
                $sqlState === '23505'
                || ($sqlState === '23000' && (
                    $driverCode === 1062
                    || (in_array($driverCode, [19, 2067], true) && str_contains(strtolower($driverMessage), 'unique'))
                ))
            ) {
                return response()->json(
                    JsendFormatter::fail(
                        'Resource already exists or conflicts with an existing unique record.',
                    ),
                    409,
                );
            }

            if (
                $request->isMethodSafe()
                && str_starts_with($sqlState, '22')
            ) {
                return response()->json(
                    JsendFormatter::fail('Invalid query parameter value.'),
                    400,
                );
            }
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
