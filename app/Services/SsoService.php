<?php

namespace App\Services;

use App\Data\SsoGroupData;
use App\Data\SsoUserData;
use Illuminate\Support\Collection;

interface SsoService
{
    /**
     * @return Collection<int, SsoUserData>
     */
    public function listUsers(array $filters = []): Collection;

    public function createUser(SsoUserData $SsoUserData): SsoUserData;

    public function getUser(string $userId): SsoUserData;

    public function updateUser(string $userId, SsoUserData $SsoUserData): SsoUserData;

    public function deleteUser(string $userId): bool;

    /**
     * @return Collection<int, SsoGroupData>
     */
    public function listGroups(array $filters = []): Collection;

    public function createGroup(SsoGroupData $SsoGroupData): SsoGroupData;

    public function getGroup(string $groupId): SsoGroupData;

    public function updateGroup(string $groupId, SsoGroupData $SsoGroupData): SsoGroupData;

    public function deleteGroup(string $groupId): bool;
}
