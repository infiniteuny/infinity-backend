<?php

namespace Tests\Feature;

use App\Data\Outline\Collection as OutlineCollection;
use App\Data\Outline\Group as OutlineGroup;
use App\Data\Outline\Invite;
use App\Enums\OutlinePermission;
use App\Services\OutlineService;
use App\Services\OutlineServiceImpl;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Spatie\LaravelData\Optional;
use Tests\TestCase;

class OutlineServiceTest extends TestCase
{
    private const BASE_URL = 'https://docs.infiniteuny.id';

    private const TOKEN = 'ol_api_test_token';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.outline.base_url' => self::BASE_URL,
            'services.outline.token' => self::TOKEN,
        ]);
    }

    public function test_service_is_bound_as_singleton(): void
    {
        $service = app(OutlineService::class);

        $this->assertInstanceOf(OutlineServiceImpl::class, $service);
        $this->assertSame($service, app(OutlineService::class));
    }

    public function test_group_user_and_collection_rpc_calls(): void
    {
        $groupId = '33333333-3333-3333-3333-333333333333';
        $collectionId = '44444444-4444-4444-4444-444444444444';
        $userId = '55555555-5555-5555-5555-555555555555';

        Http::fake([
            self::BASE_URL.'/api/groups.create' => Http::response([
                'data' => [
                    'id' => $groupId,
                    'name' => 'Core Team 2025',
                    'memberCount' => 0,
                ],
            ]),
            self::BASE_URL.'/api/users.invite' => Http::response([
                'data' => [
                    'sent' => [[
                        'name' => 'Bob',
                        'email' => 'bob@infiniteuny.id',
                        'role' => 'member',
                    ]],
                    'users' => [[
                        'id' => $userId,
                        'name' => 'Bob',
                        'email' => 'bob@infiniteuny.id',
                        'role' => 'member',
                    ]],
                ],
            ]),
            self::BASE_URL.'/api/groups.add_user' => Http::response([
                'data' => ['users' => [], 'groups' => [], 'groupMemberships' => []],
            ]),
            self::BASE_URL.'/api/collections.create' => Http::response([
                'data' => [
                    'id' => $collectionId,
                    'name' => 'Core 2025',
                ],
            ]),
            self::BASE_URL.'/api/collections.add_group' => Http::response([
                'data' => ['collectionGroupMemberships' => []],
            ]),
        ]);

        $service = app(OutlineService::class);

        $group = $service->createGroup(new OutlineGroup(
            id: Optional::create(),
            name: 'Core Team 2025',
            description: Optional::create(),
            icon: Optional::create(),
            color: Optional::create(),
            externalId: Optional::create(),
            memberCount: Optional::create(),
        ));

        $invited = $service->inviteUsers([
            new Invite(name: 'Bob', email: 'bob@infiniteuny.id'),
        ], suppressEmail: true);

        $added = $service->addUserToGroup($group->id, $invited->first()->id);

        $collection = $service->createCollection(new OutlineCollection(
            id: Optional::create(),
            name: 'Core 2025',
            description: Optional::create(),
            permission: Optional::create(),
            icon: Optional::create(),
            color: Optional::create(),
            sharing: Optional::create(),
            url: Optional::create(),
            urlId: Optional::create(),
        ));

        $service->addGroupToCollection($collection->id, $group->id, OutlinePermission::READ_WRITE);

        $this->assertSame($groupId, $group->id);
        $this->assertSame($userId, $invited->first()->id);
        $this->assertTrue($added);
        $this->assertSame($collectionId, $collection->id);

        Http::assertSent(fn (Request $r) => $r->url() === self::BASE_URL.'/api/groups.create'
            && $r->hasHeader('Authorization', 'Bearer '.self::TOKEN)
            && $r['name'] === 'Core Team 2025');

        Http::assertSent(fn (Request $r) => $r->url() === self::BASE_URL.'/api/users.invite'
            && $r['suppressEmail'] === true
            && $r['invites'][0]['email'] === 'bob@infiniteuny.id');

        Http::assertSent(fn (Request $r) => $r->url() === self::BASE_URL.'/api/groups.add_user'
            && $r['id'] === $groupId
            && $r['userId'] === $userId);

        Http::assertSent(fn (Request $r) => $r->url() === self::BASE_URL.'/api/collections.add_group'
            && $r['id'] === $collectionId
            && $r['groupId'] === $groupId
            && $r['permission'] === OutlinePermission::READ_WRITE->value);
    }
}
