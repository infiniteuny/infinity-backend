<?php

namespace App\Services;

use App\Data\SsoGroupData;
use App\Data\SsoUserData;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class SsoServiceImpl implements SsoService
{
    public function listUsers(array $filters = []): Collection
    {
        $response = Http::authentik()
            ->get('/api/v3/core/users/', $filters);

        $users = SsoUserData::collect($response->json('results', []), Collection::class);

        return $users;
    }

    public function createUser(SsoUserData $ssoUserData): SsoUserData
    {
        $response = Http::authentik()
            ->post('/api/v3/core/users/', $ssoUserData->toArray());

        return SsoUserData::from($response->json());
    }

    public function getUser(string $userId): SsoUserData
    {
        $response = Http::authentik()
            ->get("/api/v3/core/users/$userId/");

        return SsoUserData::from($response->json());
    }

    public function updateUser(string $userId, SsoUserData $ssoUserData): SsoUserData
    {
        $response = Http::authentik()
            ->patch("/api/v3/core/users/$userId/", $ssoUserData->toArray());

        return SsoUserData::from($response->json());
    }

    public function deleteUser(string $userId): bool
    {
        $response = Http::authentik()
            ->delete("/api/v3/core/users/$userId/");

        return $response->successful();
    }

    public function listGroups(array $filters = []): Collection
    {
        $response = Http::authentik()
            ->get('/api/v3/core/groups/', $filters);

        $groups = SsoGroupData::collect($response->json('results', []), Collection::class);

        return $groups;
    }

    public function createGroup(SsoGroupData $ssoGroupData): SsoGroupData
    {
        $response = Http::authentik()
            ->post('/api/v3/core/groups/', $ssoGroupData->toArray());

        return SsoGroupData::from($response->json());
    }

    public function getGroup(string $groupId): SsoGroupData
    {
        $response = Http::authentik()
            ->get("/api/v3/core/groups/$groupId/");

        return SsoGroupData::from($response->json());
    }

    public function updateGroup(string $groupId, SsoGroupData $ssoGroupData): SsoGroupData
    {
        $response = Http::authentik()
            ->patch("/api/v3/core/groups/$groupId/", $ssoGroupData->toArray());

        return SsoGroupData::from($response->json());
    }

    public function deleteGroup(string $groupId): bool
    {
        $response = Http::authentik()
            ->delete("/api/v3/core/groups/$groupId/");

        return $response->successful();
    }
}
