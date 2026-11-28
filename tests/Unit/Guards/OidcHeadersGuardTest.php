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
use Tests\TestCase;

class OidcHeadersGuardTest extends TestCase
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

    public function test_defers_quiet_case_insensitive_sso_id_binding_until_after_revocation_check(): void
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

        $originalUpdatedAt = Carbon::parse('2026-01-01 00:00:00');
        $memberGroup = Group::create(['name' => 'Member', 'is_managed' => true]);
        $memberGroup->users()->attach($alice->id);
        Group::whereKey($memberGroup->id)->update(['updated_at' => $originalUpdatedAt]);
        User::withoutTimestamps(function () use ($alice, $originalUpdatedAt) {
            $alice->updated_at = $originalUpdatedAt;
            $alice->saveQuietly();
        });

        $revokedToken = Token::create([
            'sso_id' => 'revoked-hdr-tok',
            'user_id' => $alice->id,
            'last_used_at' => now()->subMinute(),
            'created_at' => now()->subMinutes(5),
            'expires_at' => now()->addHour(),
        ]);
        $revokedToken->delete();

        $revokedRequest = Request::create('/v1/tokens', 'GET', server: [
            'HTTP_X_USER_ID' => '555',
            'HTTP_X_USER_EMAIL' => 'alice.putri@student.uny.ac.id',
            'HTTP_X_TOKEN_ID' => 'revoked-hdr-tok',
            'HTTP_X_TOKEN_CREATED_AT' => (string) time(),
            'HTTP_X_TOKEN_EXPIRES_AT' => (string) (time() + 3600),
            'HTTP_AUTHORIZATION' => 'Bearer revoked-jwt',
        ]);
        $this->app->instance('request', $revokedRequest);
        Auth::forgetGuards();

        $this->assertNull(Auth::guard('api_headers')->user());
        $this->assertNull($alice->fresh()->sso_id);

        $this->mock(OidcService::class, function ($mock) {
            $mock->shouldReceive('introspect')
                ->once()
                ->with('valid-jwt')
                ->andReturn([
                    'active' => true,
                    'sub' => '555',
                    'uid' => 'active-hdr-tok',
                    'iat' => time() - 60,
                    'exp' => time() + 3600,
                ]);
        });

        $activeRequest = Request::create('/v1/tokens', 'GET', server: [
            'HTTP_X_USER_ID' => '555',
            'HTTP_X_USER_EMAIL' => 'alice.putri@student.uny.ac.id',
            'HTTP_X_TOKEN_ID' => 'active-hdr-tok',
            'HTTP_X_TOKEN_CREATED_AT' => (string) time(),
            'HTTP_X_TOKEN_EXPIRES_AT' => (string) (time() + 3600),
            'HTTP_AUTHORIZATION' => 'Bearer valid-jwt',
        ]);
        $this->app->instance('request', $activeRequest);
        Auth::forgetGuards();

        $resolved = Auth::guard('api_headers')->user();
        $this->assertNotNull($resolved);
        $this->assertSame($alice->id, $resolved->id);

        $alice->refresh();
        $this->assertSame('555', $alice->sso_id);
        $this->assertTrue($alice->updated_at->equalTo($originalUpdatedAt));
        $this->assertTrue($memberGroup->fresh()->updated_at->gt($originalUpdatedAt));

        // 4. Subsequent uses within 60s throttle last_used_at DB writes; >= 60s updates last_used_at
        $token = Token::where('sso_id', 'active-hdr-tok')->firstOrFail();
        $recentUse = now()->subSeconds(15)->startOfSecond();
        $token->last_used_at = $recentUse;
        $token->save();

        $withinWindowRequest = Request::create('/v1/tokens', 'GET', server: [
            'HTTP_X_USER_ID' => '555',
            'HTTP_X_USER_EMAIL' => 'alice.putri@student.uny.ac.id',
            'HTTP_X_TOKEN_ID' => 'active-hdr-tok',
            'HTTP_X_TOKEN_CREATED_AT' => (string) time(),
            'HTTP_X_TOKEN_EXPIRES_AT' => (string) (time() + 3600),
            'HTTP_AUTHORIZATION' => 'Bearer valid-jwt',
        ]);
        $this->app->instance('request', $withinWindowRequest);
        Auth::forgetGuards();
        $this->assertNotNull(Auth::guard('api_headers')->user());
        $this->assertTrue($token->fresh()->last_used_at->equalTo($recentUse));

        $staleUse = now()->subSeconds(61)->startOfSecond();
        $token->last_used_at = $staleUse;
        $token->save();

        $afterWindowRequest = Request::create('/v1/tokens', 'GET', server: [
            'HTTP_X_USER_ID' => '555',
            'HTTP_X_USER_EMAIL' => 'alice.putri@student.uny.ac.id',
            'HTTP_X_TOKEN_ID' => 'active-hdr-tok',
            'HTTP_X_TOKEN_CREATED_AT' => (string) time(),
            'HTTP_X_TOKEN_EXPIRES_AT' => (string) (time() + 3600),
            'HTTP_AUTHORIZATION' => 'Bearer valid-jwt',
        ]);
        $this->app->instance('request', $afterWindowRequest);
        Auth::forgetGuards();
        $this->assertNotNull(Auth::guard('api_headers')->user());
        $this->assertTrue($token->fresh()->last_used_at->gt($staleUse));
    }

    public function test_rejects_token_bearer_smuggling_introspection_subject_mismatch_and_rebind_of_linked_user(): void
    {
        $admin = User::create([
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
        $admin->sso_id = '100';
        $admin->saveQuietly();

        Token::create([
            'sso_id' => 'known-admin-tok',
            'user_id' => $admin->id,
            'last_used_at' => now()->subMinute(),
            'created_at' => now()->subMinutes(5),
            'expires_at' => now()->addHour(),
        ]);

        // 1. "Token Bearer ..." prefix smuggling (which bypasses Envoy jwt_authn) is rejected at offset > 0
        $smuggledRequest = Request::create('/v1/users', 'GET', server: [
            'HTTP_X_USER_ID' => '100',
            'HTTP_X_USER_EMAIL' => 'admin@infiniteuny.id',
            'HTTP_X_TOKEN_ID' => 'known-admin-tok',
            'HTTP_X_TOKEN_CREATED_AT' => (string) time(),
            'HTTP_X_TOKEN_EXPIRES_AT' => (string) (time() + 3600),
            'HTTP_AUTHORIZATION' => 'Token Bearer anything',
        ]);
        $this->app->instance('request', $smuggledRequest);
        Auth::forgetGuards();
        $this->assertNull(Auth::guard('api_headers')->user());

        // 2. Introspection returning a different 'sub' than X-User-Id is rejected without minting an admin Token
        $this->mock(OidcService::class, function ($mock) {
            $mock->shouldReceive('introspect')
                ->once()
                ->with('attacker-jwt')
                ->andReturn([
                    'active' => true,
                    'sub' => '999',
                    'iat' => time() - 60,
                    'exp' => time() + 3600,
                ]);
        });

        $mismatchSubRequest = Request::create('/v1/users', 'GET', server: [
            'HTTP_X_USER_ID' => '100',
            'HTTP_X_USER_EMAIL' => 'admin@infiniteuny.id',
            'HTTP_X_TOKEN_ID' => 'forged-tok-id',
            'HTTP_X_TOKEN_CREATED_AT' => (string) time(),
            'HTTP_X_TOKEN_EXPIRES_AT' => (string) (time() + 3600),
            'HTTP_AUTHORIZATION' => 'Bearer attacker-jwt',
        ]);
        $this->app->instance('request', $mismatchSubRequest);
        Auth::forgetGuards();
        $this->assertNull(Auth::guard('api_headers')->user());
        $this->assertDatabaseMissing('tokens', ['sso_id' => 'forged-tok-id']);

        // 3. Duplicate-email Authentik account ('999') cannot overwrite already-linked admin->sso_id ('100')
        $rebindAttempt = Request::create('/v1/users', 'GET', server: [
            'HTTP_X_USER_ID' => '999',
            'HTTP_X_USER_EMAIL' => 'admin@infiniteuny.id',
            'HTTP_X_TOKEN_ID' => 'attacker-tok-id',
            'HTTP_X_TOKEN_CREATED_AT' => (string) time(),
            'HTTP_X_TOKEN_EXPIRES_AT' => (string) (time() + 3600),
            'HTTP_AUTHORIZATION' => 'Bearer attacker-jwt',
        ]);
        $this->app->instance('request', $rebindAttempt);
        Auth::forgetGuards();
        $this->assertNull(Auth::guard('api_headers')->user());
        $this->assertSame('100', $admin->fresh()->sso_id);
    }
}
