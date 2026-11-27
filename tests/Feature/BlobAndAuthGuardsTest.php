<?php

namespace Tests\Feature;

use App\Enums\StorageVisibility;
use App\Facades\Storage as BlobStorage;
use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Major;
use App\Models\Permission;
use App\Models\Token;
use App\Models\User;
use App\Services\OidcService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage as LaravelStorage;
use Illuminate\Support\Facades\URL;
use RuntimeException;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class BlobAndAuthGuardsTest extends TestCase
{
    use RefreshDatabase;

    private User $alice;

    private User $bob;

    protected function setUp(): void
    {
        parent::setUp();

        LaravelStorage::fake('local');

        $degree = Degree::create(['code' => 'S1', 'name' => 'Sarjana']);
        $faculty = Faculty::create(['code' => 'FT', 'name' => 'Teknik']);
        $major = Major::create([
            'code' => 'PTI',
            'name' => 'Pendidikan Teknik Informatika',
            'degree_id' => $degree->id,
            'faculty_id' => $faculty->id,
        ]);

        $this->alice = User::create([
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
        $this->alice->givePermissionTo(Permission::create([
            'name' => 'read-own-token',
            'guard_name' => 'api',
        ]));

        $this->bob = User::create([
            'name' => 'Bob Putra',
            'username' => 'bobputra',
            'email_address' => 'bob.putra@student.uny.ac.id',
            'phone_number' => '+6282222222222',
            'student_id' => '24520241002',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);
    }

    public function test_blob_controller_serves_files_preserves_headers_and_returns_404_on_directories_or_traversal(): void
    {
        $manifest = BlobStorage::store(
            UploadedFile::fake()->create('proposal.pdf', 12, 'application/pdf'),
            'fund-applications/proposals',
            StorageVisibility::PRIVATE,
        );
        $params = json_decode($manifest, true);
        $blobPath = $params['path'].'/'.$params['name'];

        $inlineUrl = URL::temporarySignedRoute('blobs.show', now()->addMinutes(10), ['blob' => $blobPath], false);
        $inlineResponse = $this->get($inlineUrl);
        $inlineResponse->assertOk();
        $this->assertStringContainsString('no-store', (string) $inlineResponse->headers->get('Cache-Control'));
        $this->assertSame(
            "default-src 'none'; style-src 'unsafe-inline'; sandbox",
            $inlineResponse->headers->get('Content-Security-Policy'),
        );
        $this->assertNotNull($inlineResponse->headers->get('Content-Disposition'));

        // Custom serveUsing callback omitting Content-Security-Policy must still preserve Content-Type & custom headers
        LaravelStorage::disk('local')->serveUsing(function ($request, $path) {
            return new Response('PDF-BYTES', 200, [
                'Content-Type' => 'application/pdf',
                'X-Custom-Served' => '1',
            ]);
        });
        $customServedResponse = $this->get($inlineUrl);
        $customServedResponse->assertOk();
        $this->assertSame('application/pdf', $customServedResponse->headers->get('Content-Type'));
        $this->assertSame('1', $customServedResponse->headers->get('X-Custom-Served'));
        $this->assertSame(
            "default-src 'none'; style-src 'unsafe-inline'; sandbox",
            $customServedResponse->headers->get('Content-Security-Policy'),
        );

        // Signed directory path must return 404 instead of 500
        $dirUrl = URL::temporarySignedRoute('blobs.show', now()->addMinutes(10), ['blob' => $params['path']], false);
        $this->getJson($dirUrl)->assertNotFound();

        // Signed path traversal must catch PathTraversalDetected inside try and return 404 instead of 500
        $traversalUrl = URL::temporarySignedRoute('blobs.show', now()->addMinutes(10), ['blob' => 'private/../.env'], false);
        $this->getJson($traversalUrl)->assertNotFound();
    }

    public function test_oidc_headers_guard_defers_quiet_case_insensitive_sso_id_binding_until_after_revocation_check(): void
    {
        config(['auth.defaults.guard' => 'api_headers']);
        Auth::shouldUse('api_headers');

        $originalUpdatedAt = Carbon::parse('2026-01-01 00:00:00');
        User::withoutTimestamps(function () use ($originalUpdatedAt) {
            $this->alice->updated_at = $originalUpdatedAt;
            $this->alice->saveQuietly();
        });

        // 1. Revoked token on first login MUST NOT bind sso_id onto Alice
        $revokedToken = Token::create([
            'sso_id' => 'revoked-hdr-tok',
            'user_id' => $this->alice->id,
            'last_used_at' => now()->subMinute(),
            'created_at' => now()->subMinutes(5),
            'expires_at' => now()->addHour(),
        ]);
        $revokedToken->delete();

        $this->getJson('/v1/tokens', [
            'X-User-Id' => '555',
            'X-User-Email' => 'alice.putri@student.uny.ac.id',
            'X-Token-Id' => 'revoked-hdr-tok',
            'X-Token-Created-At' => (string) time(),
            'X-Token-Expires-At' => (string) (time() + 3600),
            'Authorization' => 'Bearer revoked-jwt',
        ])->assertStatus(401);

        $this->alice->refresh();
        $this->assertNull($this->alice->sso_id);

        // 2. Valid active token on first login matches case-insensitive email, binds sso_id quietly, and preserves updated_at
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

        Auth::forgetGuards();

        $this->getJson('/v1/tokens', [
            'X-User-Id' => '555',
            'X-User-Email' => 'alice.putri@student.uny.ac.id',
            'X-Token-Id' => 'active-hdr-tok',
            'X-Token-Created-At' => (string) time(),
            'X-Token-Expires-At' => (string) (time() + 3600),
            'Authorization' => 'Bearer valid-jwt',
        ])->assertOk();

        $this->alice->refresh();
        $this->assertSame('555', $this->alice->sso_id);
        $this->assertTrue($this->alice->updated_at->equalTo($originalUpdatedAt));
    }

    public function test_oidc_token_guard_handles_missing_uid_defers_sso_id_binding_and_memoizes_failed_validation_per_request(): void
    {
        config([
            'auth.defaults.guard' => 'api_token',
            'auth.defaults.semi_public_guard' => 'api_token',
        ]);
        Auth::shouldUse('api_token');

        // 1. Missing 'uid' claim returns 401 instead of 500 Undefined array key "uid"
        $this->mock(OidcService::class, function ($mock) {
            $mock->shouldReceive('verify')
                ->once()
                ->with('missing-uid-jwt')
                ->andReturn(['sub' => '777']);
        });

        $this->getJson('/v1/tokens', [
            'Authorization' => 'Bearer missing-uid-jwt',
        ])->assertStatus(401);

        // 2. Invalid JWT on a multi-item semi-public collection (/v1/users) calls verify() ONLY ONCE across the whole request
        Auth::forgetGuards();
        $this->mock(OidcService::class, function ($mock) {
            $mock->shouldReceive('verify')
                ->once()
                ->with('bad-collection-jwt')
                ->andThrow(new RuntimeException('Expired JWT'));
        });

        $this->getJson('/v1/users', [
            'Authorization' => 'Bearer bad-collection-jwt',
        ])
            ->assertOk()
            ->assertJsonCount(2, 'data.users')
            ->assertJsonPath('data.users.0.email_address', 'REDACTED')
            ->assertJsonPath('data.users.1.email_address', 'REDACTED');

        // 3. Revoked token on first login does NOT persist sso_id; subsequent active token binds sso_id quietly
        $revoked = Token::create([
            'sso_id' => 'revoked-uid',
            'user_id' => $this->alice->id,
            'last_used_at' => now()->subMinute(),
            'created_at' => now()->subMinutes(5),
            'expires_at' => now()->addHour(),
        ]);
        $revoked->delete();

        Auth::forgetGuards();
        $this->mock(OidcService::class, function ($mock) {
            $mock->shouldReceive('verify')
                ->once()
                ->with('revoked-first-login-jwt')
                ->andReturn(['sub' => '888', 'uid' => 'revoked-uid']);
            $mock->shouldReceive('getUserInfo')
                ->once()
                ->with('revoked-first-login-jwt')
                ->andReturn(['email' => 'alice.putri@student.uny.ac.id']);
        });

        $this->getJson('/v1/tokens', [
            'Authorization' => 'Bearer revoked-first-login-jwt',
        ])->assertStatus(401);

        $this->assertNull($this->alice->fresh()->sso_id);

        $originalUpdatedAt = Carbon::parse('2026-01-01 00:00:00');
        User::withoutTimestamps(function () use ($originalUpdatedAt) {
            $this->alice->updated_at = $originalUpdatedAt;
            $this->alice->saveQuietly();
        });

        Auth::forgetGuards();
        $this->mock(OidcService::class, function ($mock) {
            $mock->shouldReceive('verify')
                ->once()
                ->with('active-first-login-jwt')
                ->andReturn(['sub' => '888', 'uid' => 'active-uid']);
            $mock->shouldReceive('getUserInfo')
                ->once()
                ->with('active-first-login-jwt')
                ->andReturn(['email' => 'alice.putri@student.uny.ac.id']);
            $mock->shouldReceive('introspect')
                ->once()
                ->with('active-first-login-jwt')
                ->andReturn([
                    'active' => true,
                    'iat' => time() - 60,
                    'exp' => time() + 3600,
                ]);
        });

        $this->getJson('/v1/tokens', [
            'Authorization' => 'Bearer active-first-login-jwt',
        ])
            ->assertOk()
            ->assertJsonCount(1, 'data.tokens');

        $this->alice->refresh();
        $this->assertSame('888', $this->alice->sso_id);
        $this->assertTrue($this->alice->updated_at->equalTo($originalUpdatedAt));
    }

    public function test_dummy_guard_handles_empty_or_invalid_dummy_user_id_safely(): void
    {
        config([
            'auth.defaults.guard' => 'dummy',
            'auth.dummy_user_id' => '',
        ]);
        Auth::forgetGuards();
        Auth::shouldUse('dummy');

        $user = Auth::guard('dummy')->user();
        $this->assertNotNull($user);
        $this->assertNotEmpty($user->id);
    }
}
