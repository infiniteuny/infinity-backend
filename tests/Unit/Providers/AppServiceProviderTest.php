<?php

namespace Tests\Unit\Providers;

use App\Models\Degree;
use App\Models\Faculty;
use App\Models\Major;
use App\Models\Token;
use App\Models\User;
use App\Providers\AppServiceProvider;
use App\Services\OidcService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use RuntimeException;
use Tests\TestCase;

class AppServiceProviderTest extends TestCase
{
    use RefreshDatabase;

    public function test_forces_https_scheme_for_https_app_url_and_sets_http_macro_timeouts(): void
    {
        $originalUrl = config('app.url');

        try {
            URL::setRequest(Request::create('http://localhost'));
            config(['app.url' => 'https://api.infiniteuny.id']);

            (new AppServiceProvider($this->app))->boot();

            $signedBlobUrl = Storage::disk('local')->temporaryUrl(
                'fund-applications/proposals/doc.pdf',
                now()->addMinutes(5),
            );

            $this->assertStringStartsWith('https://api.infiniteuny.id/static/private/fund-applications/proposals/doc.pdf?', $signedBlobUrl);
        } finally {
            config(['app.url' => $originalUrl]);
            URL::forceScheme(null);
            (new AppServiceProvider($this->app))->boot();
        }

        config([
            'services.authentik.token' => null,
            'services.authentik.base_url' => 'https://auth.infiniteuny.id',
            'services.outline.token' => null,
            'services.outline.base_url' => 'https://docs.infiniteuny.id',
        ]);

        $authentikOptions = Http::authentik()->getOptions();
        $outlineOptions = Http::outline()->getOptions();

        $this->assertSame(10, $authentikOptions['connect_timeout']);
        $this->assertSame(30, $authentikOptions['timeout']);
        $this->assertSame(10, $outlineOptions['connect_timeout']);
        $this->assertSame(30, $outlineOptions['timeout']);
    }

    public function test_api_rate_limiter_does_not_invoke_default_headers_guard_on_semi_public_routes(): void
    {
        config([
            'app.rate_limiter.enabled' => true,
            'auth.defaults.guard' => 'api_headers',
            'auth.defaults.semi_public_guard' => 'api_token',
        ]);
        Auth::shouldUse('api_headers');
        Auth::forgetGuards();

        $degree = Degree::create(['code' => 'S1', 'name' => 'Sarjana']);
        $faculty = Faculty::create(['code' => 'FT', 'name' => 'Teknik']);
        $major = Major::create([
            'code' => 'PTI',
            'name' => 'Pendidikan Teknik Informatika',
            'degree_id' => $degree->id,
            'faculty_id' => $faculty->id,
        ]);

        $victim = User::create([
            'name' => 'Victim User',
            'username' => 'victimuser',
            'email_address' => 'victim@student.uny.ac.id',
            'phone_number' => '+6281111111199',
            'student_id' => '24520241099',
            'major_id' => $major->id,
            'links' => (object) [],
            'is_member' => true,
            'is_extraordinary' => false,
        ]);
        $victim->sso_id = '999';
        $victim->saveQuietly();

        Token::create([
            'sso_id' => 'tok-rate-limit',
            'user_id' => $victim->id,
            'last_used_at' => now(),
            'created_at' => now()->subMinute(),
            'expires_at' => now()->addHour(),
        ]);

        $this->mock(OidcService::class, function ($mock) {
            $mock->shouldReceive('verify')
                ->once()
                ->with('invalid-jwt')
                ->andThrow(new RuntimeException('Invalid JWT signature'));
            $mock->shouldNotReceive('introspect');
        });

        // With RATE_LIMITER_ENABLED=true on a semi-public route, spoofed X-User-*/X-Token-* headers
        // must NOT populate Auth::hasUser() via api_headers or unredact PII!
        $this->getJson("/v1/users/{$victim->id}", [
            'X-User-Id' => '999',
            'X-User-Email' => 'victim@student.uny.ac.id',
            'X-Token-Id' => 'tok-rate-limit',
            'X-Token-Created-At' => (string) time(),
            'X-Token-Expires-At' => (string) (time() + 3600),
            'Authorization' => 'Bearer invalid-jwt',
        ])
            ->assertOk()
            ->assertJsonPath('data.user.email_address', 'REDACTED')
            ->assertJsonPath('data.user.phone_number', 'REDACTED')
            ->assertJsonPath('data.user.student_id', 'REDACTED');
    }
}
