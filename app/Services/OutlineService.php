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

interface OutlineService
{
    /**
     * @return Collection<int, User>
     */
    public function listUsers(array $filters = []): Collection;

    public function getUser(string $userId): User;

    /**
     * @param  array<int, Invite>  $invites
     * @return Collection<int, User>
     */
    public function inviteUsers(array $invites, bool $suppressEmail = false): Collection;

    /**
     * @return Collection<int, Group>
     */
    public function listGroups(array $filters = []): Collection;

    public function getGroup(string $groupId): Group;

    public function createGroup(Group $groupData): Group;

    public function updateGroup(string $groupId, Group $groupData): Group;

    public function deleteGroup(string $groupId): bool;

    /**
     * @return Collection<int, User>
     */
    public function listGroupMembers(string $groupId, array $filters = []): Collection;

    public function addUserToGroup(
        string $groupId,
        string $userId,
        ?OutlineGroupPermission $permission = null,
    ): bool;

    public function removeUserFromGroup(string $groupId, string $userId): bool;

    /**
     * @return Collection<int, OutlineCollection>
     */
    public function listCollections(array $filters = []): Collection;

    public function getCollection(string $collectionId): OutlineCollection;

    public function createCollection(OutlineCollection $collectionData): OutlineCollection;

    public function updateCollection(string $collectionId, OutlineCollection $collectionData): OutlineCollection;

    public function deleteCollection(string $collectionId): bool;

    /**
     * @return Collection<int, CollectionGroupMembership>
     */
    public function listCollectionGroups(string $collectionId, array $filters = []): Collection;

    /**
     * @return Collection<int, CollectionGroupMembership>
     */
    public function addGroupToCollection(
        string $collectionId,
        string $groupId,
        OutlinePermission $permission,
    ): Collection;

    public function removeGroupFromCollection(string $collectionId, string $groupId): bool;
}
