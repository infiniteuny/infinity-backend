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
            'team',
            'team-member',
            'fund-application',
        ];
        $permissionResourcesWithoutOwnership = [
            'degree',
            'faculty',
            'major',
            'permission',
            'group',
            'group-permission',
            'core-team-division',
            'competition-organizer-type',
            'competition-output',
            'competition-rank',
            'competition-scale',
            'competition-time-range',
            'competition-team-type',
        ];
        $otherPermissions = [
            'create-config',
            'update-config',
            'delete-config',
            'read-token',
            'read-own-token',
            'delete-token',
            'delete-own-token',
            'create-achievement',
            'create-own-achievement',
            'update-achievement',
            'update-own-achievement',
            'delete-achievement',
            'delete-own-achievement',
            'approve-achievement',
            'approve-fund-application',
            'create-community-group',
            'update-community-group',
            'delete-community-group',
            'create-community-group-member',
            'create-own-community-group-member',
            'update-community-group-member',
            'update-own-community-group-member',
            'delete-community-group-member',
            'delete-own-community-group-member',
            'create-community-group-admin',
            'update-community-group-admin',
            'delete-community-group-admin',
            'create-community-group-admin-member',
            'update-community-group-admin-member',
            'delete-community-group-admin-member',
            'create-core-team',
            'update-core-team',
            'delete-core-team',
            'create-core-team-member',
            'update-core-team-member',
            'delete-core-team-member',
            'create-competition',
            'update-competition',
            'delete-competition',
            'create-testimonial',
            'update-testimonial',
            'delete-testimonial',
            'create-project-gallery',
            'update-project-gallery',
            'delete-project-gallery',
            'create-persona',
            'update-persona',
            'delete-persona',
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

        foreach ($otherPermissions as $permission) {
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
