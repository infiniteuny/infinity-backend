<?php

namespace App\Services;

use App\Data\Outline\Collection as OutlineCollection;
use App\Data\Outline\CollectionGroupMembership;
use App\Data\Outline\Group;
use App\Data\Outline\Invite;
use App\Data\Outline\User;
use App\Enums\OutlineGroupPermission;
use App\Enums\OutlinePermission;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class OutlineServiceImpl implements OutlineService
{
    public function isConfigured(): bool
    {
        return trim((string) config('services.outline.base_url', '')) !== ''
            && trim((string) config('services.outline.token', '')) !== '';
    }

    public function listUsers(array $filters = []): Collection
    {
        $response = Http::outline()
            ->post('/api/users.list', $filters)
            ->throw();

        return User::collect($response->json('data', []), Collection::class);
    }

    public function getUser(string $userId): User
    {
        $response = Http::outline()
            ->post('/api/users.info', [
                'id' => $userId,
            ])
            ->throw();

        return User::from($response->json('data'));
    }

    public function inviteUsers(array $invites, bool $suppressEmail = false): Collection
    {
        if ($invites === []) {
            return collect();
        }

        $payload = [
            'invites' => array_map(
                fn (Invite $invite) => $invite->toArray(),
                array_values($invites),
            ),
            'suppressEmail' => $suppressEmail,
        ];

        $response = Http::outline()
            ->post('/api/users.invite', $payload)
            ->throw();

        return User::collect($response->json('data.users', []), Collection::class);
    }

    public function listGroups(array $filters = []): Collection
    {
        $response = Http::outline()
            ->post('/api/groups.list', $filters)
            ->throw();

        return Group::collect($response->json('data.groups', []), Collection::class);
    }

    public function getGroup(string $groupId): Group
    {
        $response = Http::outline()
            ->post('/api/groups.info', [
                'id' => $groupId,
            ])
            ->throw();

        return Group::from($response->json('data'));
    }

    public function createGroup(Group $groupData): Group
    {
        $response = Http::outline()
            ->post('/api/groups.create', $groupData->toArray())
            ->throw();

        return Group::from($response->json('data'));
    }

    public function updateGroup(string $groupId, Group $groupData): Group
    {
        $response = Http::outline()
            ->post('/api/groups.update', array_merge($groupData->toArray(), [
                'id' => $groupId,
            ]))
            ->throw();

        return Group::from($response->json('data'));
    }

    public function deleteGroup(string $groupId): bool
    {
        $response = Http::outline()
            ->post('/api/groups.delete', [
                'id' => $groupId,
            ])
            ->throw();

        return (bool) $response->json('success', $response->successful());
    }

    public function listGroupMembers(string $groupId, array $filters = []): Collection
    {
        $response = Http::outline()
            ->post('/api/groups.memberships', array_merge($filters, [
                'id' => $groupId,
            ]))
            ->throw();

        return User::collect($response->json('data.users', []), Collection::class);
    }

    public function addUserToGroup(
        string $groupId,
        string $userId,
        ?OutlineGroupPermission $permission = null,
    ): bool {
        $payload = [
            'id' => $groupId,
            'userId' => $userId,
        ];

        if ($permission !== null) {
            $payload['permission'] = $permission->value;
        }

        $response = Http::outline()
            ->post('/api/groups.add_user', $payload)
            ->throw();

        return $response->successful();
    }

    public function removeUserFromGroup(string $groupId, string $userId): bool
    {
        $response = Http::outline()
            ->post('/api/groups.remove_user', [
                'id' => $groupId,
                'userId' => $userId,
            ])
            ->throw();

        return $response->successful();
    }

    public function listCollections(array $filters = []): Collection
    {
        $response = Http::outline()
            ->post('/api/collections.list', $filters)
            ->throw();

        return OutlineCollection::collect($response->json('data', []), Collection::class);
    }

    public function getCollection(string $collectionId): OutlineCollection
    {
        $response = Http::outline()
            ->post('/api/collections.info', [
                'id' => $collectionId,
            ])
            ->throw();

        return OutlineCollection::from($response->json('data'));
    }

    public function createCollection(OutlineCollection $collectionData): OutlineCollection
    {
        $response = Http::outline()
            ->post('/api/collections.create', $collectionData->toArray())
            ->throw();

        return OutlineCollection::from($response->json('data'));
    }

    public function updateCollection(string $collectionId, OutlineCollection $collectionData): OutlineCollection
    {
        $response = Http::outline()
            ->post('/api/collections.update', array_merge($collectionData->toArray(), [
                'id' => $collectionId,
            ]))
            ->throw();

        return OutlineCollection::from($response->json('data'));
    }

    public function deleteCollection(string $collectionId): bool
    {
        $response = Http::outline()
            ->post('/api/collections.delete', [
                'id' => $collectionId,
            ])
            ->throw();

        return (bool) $response->json('success', $response->successful());
    }

    public function listCollectionGroups(string $collectionId, array $filters = []): Collection
    {
        $response = Http::outline()
            ->post('/api/collections.group_memberships', array_merge($filters, [
                'id' => $collectionId,
            ]))
            ->throw();

        return CollectionGroupMembership::collect(
            $response->json('data.groupMemberships') ?? $response->json('data.collectionGroupMemberships', []),
            Collection::class,
        );
    }

    public function addGroupToCollection(
        string $collectionId,
        string $groupId,
        OutlinePermission $permission,
    ): Collection {
        $response = Http::outline()
            ->post('/api/collections.add_group', [
                'id' => $collectionId,
                'groupId' => $groupId,
                'permission' => $permission->value,
            ])
            ->throw();

        return CollectionGroupMembership::collect(
            $response->json('data.groupMemberships') ?? $response->json('data.collectionGroupMemberships', []),
            Collection::class,
        );
    }

    public function removeGroupFromCollection(string $collectionId, string $groupId): bool
    {
        $response = Http::outline()
            ->post('/api/collections.remove_group', [
                'id' => $collectionId,
                'groupId' => $groupId,
            ])
            ->throw();

        return (bool) $response->json('success', $response->successful());
    }
}
