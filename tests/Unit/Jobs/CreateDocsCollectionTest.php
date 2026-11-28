<?php

namespace Tests\Unit\Jobs;

use App\Enums\OutlinePermission;
use App\Jobs\CreateDocsCollection;
use App\Models\Group;
use App\Models\GroupIntegration;
use App\Services\OutlineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CreateDocsCollectionTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = 'https://docs.infiniteuny.id';

    private const CORE_TEAM_GROUP_ID = '11111111-1111-1111-1111-111111111111';

    private const EX_CORE_TEAM_GROUP_ID = '22222222-2222-2222-2222-222222222222';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.outline.base_url' => self::BASE_URL,
            'services.outline.token' => 'ol_test_token',
        ]);
    }

    public function test_adopts_and_locks_down_public_collection_and_grants_editor_and_viewer_permissions(): void
    {
        $docsGroupId = '33333333-3333-3333-3333-333333333333';
        $docsCollectionId = '44444444-4444-4444-4444-444444444444';

        Http::fake([
            self::BASE_URL.'/api/collections.list' => Http::response([
                'data' => [[
                    'id' => $docsCollectionId,
                    'name' => 'Core Team 2025',
                    'permission' => 'read_write',
                ]],
            ]),
            self::BASE_URL.'/api/collections.update' => Http::response([
                'data' => [
                    'id' => $docsCollectionId,
                    'name' => 'Core Team 2025',
                    'permission' => null,
                ],
            ]),
            self::BASE_URL.'/api/collections.add_group' => Http::response([
                'data' => ['groupMemberships' => []],
            ]),
        ]);

        $group = Group::create(['name' => 'Core Team 2025']);
        GroupIntegration::create([
            'group_id' => $group->id,
            'docs_group_id' => $docsGroupId,
        ]);

        (new CreateDocsCollection($group, 'Core Team 2025', [self::CORE_TEAM_GROUP_ID, self::EX_CORE_TEAM_GROUP_ID]))
            ->handle(app(OutlineService::class));

        $integration = $group->fresh()->integration;
        $this->assertSame($docsCollectionId, $integration->docs_collection_id);
        $this->assertNotNull($integration->docs_collection_last_synced_at);

        Http::assertSent(fn (Request $r) => $r->url() === self::BASE_URL.'/api/collections.update'
            && $r['id'] === $docsCollectionId
            && array_key_exists('permission', $r->data())
            && $r['permission'] === null);

        Http::assertSent(fn (Request $r) => $r->url() === self::BASE_URL.'/api/collections.add_group'
            && $r['groupId'] === $docsGroupId
            && $r['permission'] === OutlinePermission::READ_WRITE->value);

        Http::assertSent(fn (Request $r) => $r->url() === self::BASE_URL.'/api/collections.add_group'
            && $r['groupId'] === self::CORE_TEAM_GROUP_ID
            && $r['permission'] === OutlinePermission::READ->value);
    }
}
