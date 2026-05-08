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
            'approve-achievement',
            'approve-fund-application',
            'create-config',
            'update-config',
            'delete-config',
            'create-user',
            'update-user',
            'update-own-user',
            'delete-user',
            'delete-own-user',
            'manage-user-membership',
            'create-user-persona',
            'create-own-user-persona',
            'update-user-persona',
            'update-own-user-persona',
            'delete-user-persona',
            'delete-own-user-persona',
            'create-user-group',
            'read-user-group',
            'read-own-user-group',
            'update-user-group',
            'delete-user-group',
            'create-user-permission',
            'read-user-permission',
            'read-own-user-permission',
            'update-user-permission',
            'delete-user-permission',
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
