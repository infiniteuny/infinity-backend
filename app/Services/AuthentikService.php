<?php

namespace App\Services;

use App\Data\Authentik\Group;
use App\Data\Authentik\User;
use Illuminate\Support\Collection;

interface AuthentikService
{
    /**
     * @return Collection<int, User>
     */
    public function listUsers(array $filters = []): Collection;

    public function createUser(User $authentikUser): User;

    public function getUser(string $userId): User;

    public function updateUser(string $userId, User $authentikUser): User;

    public function deleteUser(string $userId): bool;

    /**
     * @return Collection<int, Group>
     */
    public function listGroups(array $filters = []): Collection;

    public function createGroup(Group $authentikGroup): Group;

    public function getGroup(string $groupId): Group;

    public function updateGroup(string $groupId, Group $authentikGroup): Group;

    public function deleteGroup(string $groupId): bool;
}
