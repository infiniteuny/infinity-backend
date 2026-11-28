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
use Tests\TestCase;

class OidcHeadersGuardTest extends TestCase
{
    use RefreshDatabase;

    public function test_defers_quiet_case_insensitive_sso_id_binding_until_after_revocation_check(): void
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

        $originalUpdatedAt = Carbon::parse('2026-01-01 00:00:00');
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
    }
}
