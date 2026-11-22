<?php

namespace App\Services;

use App\Data\Authentik\Group;
use App\Data\Authentik\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Http;

class AuthentikServiceImpl implements AuthentikService
{
    public function listUsers(array $filters = []): Collection
    {
        $response = Http::authentik()
            ->get('/api/v3/core/users/', $filters);

        $users = User::collect($response->json('results', []), Collection::class);

        return $users;
    }

    public function createUser(User $authentikUser): User
    {
        $response = Http::authentik()
            ->post('/api/v3/core/users/', $authentikUser->toArray());

        return User::from($response->json());
    }

    public function getUser(string $userId): User
    {
        $response = Http::authentik()
            ->get("/api/v3/core/users/$userId/");

        return User::from($response->json());
    }

    public function updateUser(string $userId, User $authentikUser): User
    {
        $response = Http::authentik()
            ->patch("/api/v3/core/users/$userId/", $authentikUser->toArray());

        return User::from($response->json());
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

        $groups = Group::collect($response->json('results', []), Collection::class);

        return $groups;
    }

    public function createGroup(Group $authentikGroup): Group
    {
        $response = Http::authentik()
            ->post('/api/v3/core/groups/', $authentikGroup->toArray());

        return Group::from($response->json());
    }

    public function getGroup(string $groupId): Group
    {
        $response = Http::authentik()
            ->get("/api/v3/core/groups/$groupId/");

        return Group::from($response->json());
    }

    public function updateGroup(string $groupId, Group $authentikGroup): Group
    {
        $response = Http::authentik()
            ->patch("/api/v3/core/groups/$groupId/", $authentikGroup->toArray());

        return Group::from($response->json());
    }

    public function deleteGroup(string $groupId): bool
    {
        $response = Http::authentik()
            ->delete("/api/v3/core/groups/$groupId/");

        return $response->successful();
    }
}
