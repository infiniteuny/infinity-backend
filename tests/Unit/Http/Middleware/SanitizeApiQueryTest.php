<?php

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\SanitizeApiQuery;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class SanitizeApiQueryTest extends TestCase
{
    public function test_rejects_null_bytes_nested_filter_arrays_and_malformed_cursors_with_400(): void
    {
        $middleware = new SanitizeApiQuery;
        $next = static fn () => new Response('ok', 200);

        $nullByteRequest = Request::create('/v1/users', 'GET', ['filters' => ['name' => "alice\0bob"]]);
        $this->assertSame(400, $middleware->handle($nullByteRequest, $next)->getStatusCode());

        $nestedFilterRequest = Request::create('/v1/users', 'GET', ['filters' => ['major_id' => [['nested']]]]);
        $this->assertSame(400, $middleware->handle($nestedFilterRequest, $next)->getStatusCode());

        $malformedCursorRequest = Request::create('/v1/users', 'GET', ['cursor' => 'not-valid-base64!!!']);
        $this->assertSame(400, $middleware->handle($malformedCursorRequest, $next)->getStatusCode());

        $validOneDimensionalFilterRequest = Request::create('/v1/users', 'GET', ['filters' => ['major_id' => ['id-1', 'id-2']]]);
        $this->assertSame(200, $middleware->handle($validOneDimensionalFilterRequest, $next)->getStatusCode());
    }
}
