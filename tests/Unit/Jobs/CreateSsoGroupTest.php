<?php

namespace Tests\Unit\Jobs;

use App\Jobs\CreateSsoGroup;
use App\Models\Group;
use App\Services\AuthentikService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CreateSsoGroupTest extends TestCase
{
    use RefreshDatabase;

    private const AUTHENTIK_URL = 'https://auth.infiniteuny.id';

    public function test_skips_substring_group_matches_omits_empty_parents_and_saves_watermark_quietly(): void
    {
        config([
            'services.authentik.base_url' => self::AUTHENTIK_URL,
            'services.authentik.token' => 'ak_test_token',
        ]);

        $pastTime = Carbon::now()->subHour()->startOfSecond();
        $group = Group::create(['name' => 'Core Team 2026', 'is_managed' => true]);
        Group::whereKey($group->id)->update(['updated_at' => $pastTime]);
        $group->refresh();

        Http::fake([
            self::AUTHENTIK_URL.'/api/v3/core/groups/*' => function (Request $request) {
                if ($request->method() === 'GET') {
                    return Http::response([
                        'results' => [[
                            'pk' => 'substring-only',
                            'name' => 'Core Team 2026 Archive',
                            'users' => [],
                        ]],
                    ]);
                }

                $this->assertArrayNotHasKey('parents', $request->data());
                $this->assertSame('Core Team 2026', $request['name']);

                return Http::response([
                    'pk' => 'created-ct-2026-uuid',
                    'name' => 'Core Team 2026',
                    'users' => [],
                ]);
            },
        ]);

        (new CreateSsoGroup($group, [null]))->handle(app(AuthentikService::class));

        $group->refresh();
        $this->assertSame('created-ct-2026-uuid', $group->sso_id);
        $this->assertNotNull($group->sso_last_synced_at);
        $this->assertTrue($group->updated_at->equalTo($pastTime));
    }
}
