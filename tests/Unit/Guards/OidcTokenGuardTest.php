<?php

namespace Tests\Unit\Guards;

use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Major;
use App\Models\Token;
use App\Models\User;
use App\Services\OidcService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use RuntimeException;
use Tests\TestCase;

class OidcTokenGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_handles_missing_uid_memoizes_failed_validation_and_defers_quiet_sso_id_binding(): void
    {
        $degree = Degree::create(['code' => 'S1', 'name' => 'Sarjana']);
        $faculty = Faculty::create(['code' => 'FT', 'name' => 'Teknik']);
        $major = Major::create([
            'code' => 'PTI',
            'name' => 'Pendidikan Teknik Informatika',
            'degree_id' => $degree->id,
            'faculty_id' => $faculty->id,
        ]);

        $alice = User::create([
            'name' => 'Alice Putri',
            'username' => 'aliceputri',
            'email_address' => 'Alice.Putri@student.uny.ac.id',
            'phone_number' => '+6281111111111',
            'student_id' => '24520241001',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);

        // 1. Missing 'uid' claim returns null instead of throwing Undefined array key
        $this->mock(OidcService::class, function ($mock) {
            $mock->shouldReceive('verify')
                ->once()
                ->with('missing-uid-jwt')
                ->andReturn(['sub' => '777']);
        });

        $this->app->instance('request', Request::create('/v1/tokens', 'GET', server: [
            'HTTP_AUTHORIZATION' => 'Bearer missing-uid-jwt',
        ]));
        Auth::forgetGuards();
        $this->assertNull(Auth::guard('api_token')->user());

        // 2. Repeated check()/user() calls on same request memoize failed verify()
        $this->mock(OidcService::class, function ($mock) {
            $mock->shouldReceive('verify')
                ->once()
                ->with('bad-jwt')
                ->andThrow(new RuntimeException('Expired JWT'));
        });

        $this->app->instance('request', Request::create('/v1/users', 'GET', server: [
            'HTTP_AUTHORIZATION' => 'Bearer bad-jwt',
        ]));
        Auth::forgetGuards();
        $guard = Auth::guard('api_token');
        $this->assertFalse($guard->check());
        $this->assertFalse($guard->check());
        $this->assertNull($guard->user());

        // 3. Revoked token does not bind sso_id; active token binds sso_id quietly
        $revoked = Token::create([
            'sso_id' => 'revoked-uid',
            'user_id' => $alice->id,
            'last_used_at' => now()->subMinute(),
            'created_at' => now()->subMinutes(5),
            'expires_at' => now()->addHour(),
        ]);
        $revoked->delete();

        $this->mock(OidcService::class, function ($mock) {
            $mock->shouldReceive('verify')
                ->once()
                ->with('revoked-jwt')
                ->andReturn(['sub' => '888', 'uid' => 'revoked-uid']);
            $mock->shouldReceive('getUserInfo')
                ->once()
                ->with('revoked-jwt')
                ->andReturn(['email' => 'alice.putri@student.uny.ac.id']);
        });

        $this->app->instance('request', Request::create('/v1/tokens', 'GET', server: [
            'HTTP_AUTHORIZATION' => 'Bearer revoked-jwt',
        ]));
        Auth::forgetGuards();
        $this->assertNull(Auth::guard('api_token')->user());
        $this->assertNull($alice->fresh()->sso_id);

        $originalUpdatedAt = Carbon::parse('2026-01-01 00:00:00');
        User::withoutTimestamps(function () use ($alice, $originalUpdatedAt) {
            $alice->updated_at = $originalUpdatedAt;
            $alice->saveQuietly();
        });

        $this->mock(OidcService::class, function ($mock) {
            $mock->shouldReceive('verify')
                ->once()
                ->with('active-jwt')
                ->andReturn(['sub' => '888', 'uid' => 'active-uid']);
            $mock->shouldReceive('getUserInfo')
                ->once()
                ->with('active-jwt')
                ->andReturn(['email' => 'alice.putri@student.uny.ac.id']);
            $mock->shouldReceive('introspect')
                ->once()
                ->with('active-jwt')
                ->andReturn([
                    'active' => true,
                    'iat' => time() - 60,
                    'exp' => time() + 3600,
                ]);
        });

        $this->app->instance('request', Request::create('/v1/tokens', 'GET', server: [
            'HTTP_AUTHORIZATION' => 'Bearer active-jwt',
        ]));
        Auth::forgetGuards();
        $this->assertSame($alice->id, Auth::guard('api_token')->user()?->id);

        $alice->refresh();
        $this->assertSame('888', $alice->sso_id);
        $this->assertTrue($alice->updated_at->equalTo($originalUpdatedAt));
    }
}
