<?php

namespace Tests\Unit\Guards;

use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Group;
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

    private Major $major;

    protected function setUp(): void
    {
        parent::setUp();

        $degree = Degree::create(['code' => 'S1', 'name' => 'Sarjana']);
        $faculty = Faculty::create(['code' => 'FT', 'name' => 'Teknik']);
        $this->major = Major::create([
            'code' => 'PTI',
            'name' => 'Pendidikan Teknik Informatika',
            'degree_id' => $degree->id,
            'faculty_id' => $faculty->id,
        ]);
    }

    public function test_handles_missing_uid_memoizes_failed_validation_and_defers_quiet_sso_id_binding(): void
    {
        $alice = User::create([
            'name' => 'Alice Putri',
            'username' => 'aliceputri',
            'email_address' => 'Alice.Putri@student.uny.ac.id',
            'phone_number' => '+6281111111111',
            'student_id' => '24520241001',
            'major_id' => $this->major->id,
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
        $memberGroup = Group::create(['name' => 'Member', 'is_managed' => true]);
        $memberGroup->users()->attach($alice->id);
        Group::whereKey($memberGroup->id)->update(['updated_at' => $originalUpdatedAt]);
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
        $this->assertTrue($memberGroup->fresh()->updated_at->gt($originalUpdatedAt));

        // 4. Subsequent uses within 60s throttle last_used_at DB writes; >= 60s updates last_used_at
        $token = Token::where('sso_id', 'active-uid')->firstOrFail();
        $recentUse = now()->subSeconds(10)->startOfSecond();
        $token->last_used_at = $recentUse;
        $token->save();

        $this->mock(OidcService::class, function ($mock) {
            $mock->shouldReceive('verify')
                ->twice()
                ->with('active-jwt')
                ->andReturn(['sub' => '888', 'uid' => 'active-uid']);
        });

        $this->app->instance('request', Request::create('/v1/tokens', 'GET', server: [
            'HTTP_AUTHORIZATION' => 'Bearer active-jwt',
        ]));
        Auth::forgetGuards();
        $this->assertSame($alice->id, Auth::guard('api_token')->user()?->id);
        $this->assertTrue($token->fresh()->last_used_at->equalTo($recentUse));

        $staleUse = now()->subSeconds(65)->startOfSecond();
        $token->last_used_at = $staleUse;
        $token->save();

        $this->app->instance('request', Request::create('/v1/tokens', 'GET', server: [
            'HTTP_AUTHORIZATION' => 'Bearer active-jwt',
        ]));
        Auth::forgetGuards();
        $this->assertSame($alice->id, Auth::guard('api_token')->user()?->id);
        $this->assertTrue($token->fresh()->last_used_at->gt($staleUse));
    }

    public function test_allows_authentik_default_unverified_email_on_unbound_user_and_prevents_overwriting_already_linked_sso_id(): void
    {
        $linkedAdmin = User::create([
            'name' => 'Admin User',
            'username' => 'adminuser',
            'email_address' => 'admin@infiniteuny.id',
            'phone_number' => '+6281111111112',
            'student_id' => '24520241002',
            'major_id' => $this->major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);
        $linkedAdmin->sso_id = '100';
        $linkedAdmin->saveQuietly();

        $unboundMember = User::create([
            'name' => 'Unbound Member',
            'username' => 'unboundmember',
            'email_address' => 'unbound@infiniteuny.id',
            'phone_number' => '+6281111111113',
            'student_id' => '24520241003',
            'major_id' => $this->major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);

        // 1. Already-linked user cannot be re-bound by a different Authentik 'sub' sharing the same email
        $this->mock(OidcService::class, function ($mock) {
            $mock->shouldReceive('verify')
                ->once()
                ->with('dup-email-jwt')
                ->andReturn(['sub' => '999', 'uid' => 'dup-uid']);
            $mock->shouldReceive('getUserInfo')
                ->once()
                ->with('dup-email-jwt')
                ->andReturn(['email' => 'admin@infiniteuny.id', 'email_verified' => true]);
            $mock->shouldNotReceive('introspect');
        });

        $this->app->instance('request', Request::create('/v1/users', 'GET', server: [
            'HTTP_AUTHORIZATION' => 'Bearer dup-email-jwt',
        ]));
        Auth::forgetGuards();
        $this->assertNull(Auth::guard('api_token')->user());
        $this->assertSame('100', $linkedAdmin->fresh()->sso_id);

        // 2. Authentik default email scope (email_verified === false) still binds an unbound local user
        $this->mock(OidcService::class, function ($mock) {
            $mock->shouldReceive('verify')
                ->once()
                ->with('authentik-default-email-jwt')
                ->andReturn(['sub' => '777', 'uid' => 'authentik-default-uid']);
            $mock->shouldReceive('getUserInfo')
                ->once()
                ->with('authentik-default-email-jwt')
                ->andReturn(['email' => 'unbound@infiniteuny.id', 'email_verified' => false]);
            $mock->shouldReceive('introspect')
                ->once()
                ->with('authentik-default-email-jwt')
                ->andReturn([
                    'active' => true,
                    'sub' => '777',
                    'uid' => 'authentik-default-uid',
                    'iat' => time() - 60,
                    'exp' => time() + 3600,
                ]);
        });

        $this->app->instance('request', Request::create('/v1/users', 'GET', server: [
            'HTTP_AUTHORIZATION' => 'Bearer authentik-default-email-jwt',
        ]));
        Auth::forgetGuards();
        $this->assertSame($unboundMember->id, Auth::guard('api_token')->user()?->id);
        $this->assertSame('777', $unboundMember->fresh()->sso_id);
    }
}
