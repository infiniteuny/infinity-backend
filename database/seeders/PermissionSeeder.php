<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Guard;
use Spatie\Permission\PermissionRegistrar;

class PermissionSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $permissionActionsWithOwnership = [
            'create',
            'create-own',
            'read',
            'read-own',
            'update',
            'update-own',
            'delete',
            'delete-own',
        ];
        $permissionActionsWithoutOwnership = [
            'create',
            'read',
            'update',
            'delete',
        ];
        $permissionResourcesWithOwnership = [
            'user-persona',
            'community-group-member',
            'team',
            'team-member',
            'fund-application',
            'achievement',
        ];
        $permissionResourcesWithoutOwnership = [
            'degree',
            'faculty',
            'major',
            'permission',
            'group',
            'group-permission',
            'user-permission',
            'user-group',
            'persona',
            'community-group',
            'community-group-admin',
            'community-group-admin-member',
            'core-team',
            'core-team-division',
            'core-team-member',
            'competition',
            'competition-organizer-type',
            'competition-output',
            'competition-rank',
            'competition-scale',
            'competition-time-range',
            'competition-team-type',
            'testimonal',
            'project-gallery',
        ];
        $specialPermissions = [
            'create-config',
            'update-config',
            'delete-config',
            'create-user',
            'read-user',
            'read-own-user',
            'update-user',
            'update-own-user',
            'delete-user',
            'delete-own-user',
            'read-own-user-group',
            'read-own-user-permission',
            'read-token',
            'read-own-token',
            'delete-token',
            'delete-own-token',
        ];

        // Reset cached roles and permissions
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($permissionResourcesWithOwnership as $resource) {
            foreach ($permissionActionsWithOwnership as $action) {
                Permission::firstOrCreate([
                    'name' => $action.'-'.$resource,
                    'guard_name' => Guard::getDefaultName(User::class),
                ]);
            }
        }

        foreach ($permissionResourcesWithoutOwnership as $resource) {
            foreach ($permissionActionsWithoutOwnership as $action) {
                Permission::firstOrCreate([
                    'name' => $action.'-'.$resource,
                    'guard_name' => Guard::getDefaultName(User::class),
                ]);
            }
        }

        foreach ($specialPermissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => Guard::getDefaultName(User::class),
            ]);
        }

        // Update cache to know about the newly created permissions
        // (required if using WithoutModelEvents in seeders)
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
