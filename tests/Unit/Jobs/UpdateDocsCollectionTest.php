<?php

namespace Tests\Unit\Jobs;

use App\Enums\OutlinePermission;
use App\Jobs\UpdateDocsCollection;
use App\Models\Group;
use App\Models\GroupIntegration;
use App\Services\OutlineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class UpdateDocsCollectionTest extends TestCase
{
    use RefreshDatabase;

    private const BASE_URL = 'https://docs.infiniteuny.id';

    private const CORE_TEAM_GROUP_ID = '11111111-1111-1111-1111-111111111111';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.outline.base_url' => self::BASE_URL,
            'services.outline.token' => 'ol_test_token',
        ]);
    }

    public function test_preserves_existing_collection_groups_and_owning_group_read_write_permission(): void
    {
        $docsGroupId = '33333333-3333-3333-3333-333333333333';
        $docsCollectionId = '44444444-4444-4444-4444-444444444444';

        Http::fake([
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
            'docs_collection_id' => $docsCollectionId,
            'docs_group_last_synced_at' => now()->subHour(),
            'docs_collection_last_synced_at' => now()->subHour(),
        ]);

        (new UpdateDocsCollection($group, 'Core Team 2025', [$docsGroupId, self::CORE_TEAM_GROUP_ID]))
            ->handle(app(OutlineService::class));

        Http::assertSent(fn (Request $r) => $r->url() === self::BASE_URL.'/api/collections.add_group'
            && $r['groupId'] === $docsGroupId
            && $r['permission'] === OutlinePermission::READ_WRITE->value);

        Http::assertNotSent(fn (Request $r) => $r->url() === self::BASE_URL.'/api/collections.add_group'
            && $r['groupId'] === $docsGroupId
            && $r['permission'] === OutlinePermission::READ->value);

        Http::assertNotSent(fn (Request $r) => $r->url() === self::BASE_URL.'/api/collections.remove_group');
    }
}
