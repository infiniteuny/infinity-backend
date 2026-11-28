<?php

namespace Tests\Unit\Jobs;

use App\Jobs\UpdateSsoGroup;
use App\Models\Group;
use App\Services\AuthentikService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UpdateSsoGroupTest extends TestCase
{
    use RefreshDatabase;

    private const AUTHENTIK_URL = 'https://auth.infiniteuny.id';

    public function test_recovers_on_404_without_substring_adoption_omits_empty_parents_and_saves_quietly(): void
    {
        config([
            'services.authentik.base_url' => self::AUTHENTIK_URL,
            'services.authentik.token' => 'ak_test_token',
        ]);

        $pastTime = Carbon::now()->subHour()->startOfSecond();
        $group = Group::create(['name' => 'Core Team 2024', 'is_managed' => true]);
        Group::withoutTimestamps(function () use ($group) {
            $group->sso_id = 'stale-sso-uuid';
            $group->saveQuietly();
        });
        Group::whereKey($group->id)->update(['updated_at' => $pastTime]);
        $group->refresh();

        Http::fake([
            self::AUTHENTIK_URL.'/api/v3/core/groups/stale-sso-uuid/' => Http::response(['detail' => 'Not found.'], 404),
            self::AUTHENTIK_URL.'/api/v3/core/groups/*' => function (Request $request) {
                if ($request->method() === 'GET') {
                    return Http::response([
                        'results' => [[
                            'pk' => 'wrong-substring-group',
                            'name' => 'Core Team 2024 Backup',
                            'users' => [],
                        ]],
                    ]);
                }

                $this->assertArrayNotHasKey('parents', $request->data());

                return Http::response([
                    'pk' => 'recreated-ct-2024-uuid',
                    'name' => 'Core Team 2024',
                    'users' => [],
                ]);
            },
        ]);

        (new UpdateSsoGroup($group, [null]))->handle(app(AuthentikService::class));

        $group->refresh();
        $this->assertSame('recreated-ct-2024-uuid', $group->sso_id);
        $this->assertNotNull($group->sso_last_synced_at);
        $this->assertTrue($group->updated_at->equalTo($pastTime));
    }
}
