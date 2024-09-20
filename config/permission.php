<?php

use App\Models\Group;
use App\Models\Permission;

return [

    'models' => [

        /*
        |-----------------------------------------------------------------------
        | Permission Model
        |-----------------------------------------------------------------------
        |
        | When using the "HasPermissions" trait from this package, we need to know which
        | Eloquent model should be used to retrieve your permissions. Of course, it
        | is often just the "Permission" model but you may use whatever you like.
        |
        | The model you want to use as a Permission model needs to implement the
        | `Spatie\Permission\Contracts\Permission` contract.
        |
        */

        'permission' => Permission::class,

        /*
        |-----------------------------------------------------------------------
        | Role Model
        |-----------------------------------------------------------------------
        |
        | When using the "HasRoles" trait from this package, we need to know which
        | Eloquent model should be used to retrieve your roles. Of course, it
        | is often just the "Role" model but you may use whatever you like.
        |
        | The model you want to use as a Role model needs to implement the
        | `Spatie\Permission\Contracts\Role` contract.
        */

        'role' => Group::class,

    ],

    'table_names' => [

        /*
        |-----------------------------------------------------------------------
        | Role Table Names
        |-----------------------------------------------------------------------
        |
        | When using the "HasRoles" trait from this package, we need to know which
        | table should be used to retrieve your roles. We have chosen a basic
        | default value but you may easily change it to any table you like.
        |
        */

        'roles' => 'groups',

        /*
        |-----------------------------------------------------------------------
        | Permission Table Name
        |-----------------------------------------------------------------------
        |
        | When using the "HasPermissions" trait from this package, we need to know which
        | table should be used to retrieve your permissions. We have chosen a basic
        | default value but you may easily change it to any table you like.
        |
        */

        'permissions' => 'permissions',

        /*
        |-----------------------------------------------------------------------
        | Model Has Permissions Table
        |-----------------------------------------------------------------------
        |
        | When using the "HasPermissions" trait from this package, we need to know which
        | table should be used to retrieve your models permissions. We have chosen a
        | basic default value but you may easily change it to any table you like.
        |
        */

        'model_has_permissions' => 'user_permissions',

        /*
        |-----------------------------------------------------------------------
        | Model Has Roles Table
        |-----------------------------------------------------------------------
        |
        | When using the "HasRoles" trait from this package, we need to know which
        | table should be used to retrieve your models roles. We have chosen a
        | basic default value but you may easily change it to any table you like.
        |
        */

        'model_has_roles' => 'user_groups',

        /*
        |-----------------------------------------------------------------------
        | Role Has Permissions Table
        |-----------------------------------------------------------------------
        |
        | When using the "HasRoles" trait from this package, we need to know which
        | table should be used to retrieve your roles permissions. We have chosen a
        | basic default value but you may easily change it to any table you like.
        |
        */

        'role_has_permissions' => 'group_permissions',
    ],

    'column_names' => [
        /*
        |-----------------------------------------------------------------------
        | Pivot Key
        |-----------------------------------------------------------------------
        |
        | Change this if you want to name the related pivots other than defaults.
        |
        */

        'role_pivot_key' => 'group_id',
        'permission_pivot_key' => 'permission_id',

        /*
        |-----------------------------------------------------------------------
        | Morph Key
        |-----------------------------------------------------------------------
        |
        | Change this if you want to name the related model primary key other than
        | `model_id`.
        |
        | For example, this would be nice if your primary keys are all UUIDs. In
        | that case, name this `model_uuid`.
        |
        */

        'model_morph_key' => 'user_id',

        /*
        |-----------------------------------------------------------------------
        | Foreign Key
        |-----------------------------------------------------------------------
        |
        | Change this if you want to use the teams feature and your related model's
        | foreign key is other than `team_id`.
        |
        */

        'team_foreign_key' => 'team_id',
    ],

    /*
    |---------------------------------------------------------------------------
    | Permission Check Method
    |---------------------------------------------------------------------------
    |
    | When set to true, the method for checking permissions will be registered on the gate.
    | Set this to false if you want to implement custom logic for checking permissions.
    |
    */

    'register_permission_check_method' => true,

    /*
    |---------------------------------------------------------------------------
    | Octane Reset Listener
    |---------------------------------------------------------------------------
    |
    | When set to true, Laravel\Octane\Events\OperationTerminated event listener will be registered
    | this will refresh permissions on every TickTerminated, TaskTerminated and RequestTerminated
    | NOTE: This should not be needed in most cases, but an Octane/Vapor combination benefited from it.
    |
    */

    'register_octane_reset_listener' => true,

    /*
    |---------------------------------------------------------------------------
    | Teams Feature
    |---------------------------------------------------------------------------
    |
    | When set to true the package implements teams using the 'team_foreign_key'.
    | If you want the migrations to register the 'team_foreign_key', you must
    | set this to true before doing the migration.
    | If you already did the migration then you must make a new migration to also
    | add 'team_foreign_key' to 'roles', 'model_has_roles', and 'model_has_permissions'
    | (view the latest version of this package's migration file)
    |
    */

    'teams' => false,

    /*
    |---------------------------------------------------------------------------
    | Passport Client Credentials Grant
    |---------------------------------------------------------------------------
    |
    | When set to true the package will use Passports Client to check permissions
    |
    */

    'use_passport_client_credentials' => false,

    /*
    |---------------------------------------------------------------------------
    | Display Permission in Exception
    |---------------------------------------------------------------------------
    |
    | When set to true, the required permission names are added to exception messages.
    | This could be considered an information leak in some contexts, so the default
    | setting is false here for optimum safety.
    |
    */

    'display_permission_in_exception' => false,

    /*
    |---------------------------------------------------------------------------
    | Display Role in Exception
    |---------------------------------------------------------------------------
    |
    | When set to true, the required role names are added to exception messages.
    | This could be considered an information leak in some contexts, so the default
    | setting is false here for optimum safety.
    |
    */

    'display_role_in_exception' => false,

    /*
    |---------------------------------------------------------------------------
    | Wildcard Permission Feature
    |---------------------------------------------------------------------------
    |
    | By default wildcard permission lookups are disabled.
    | See documentation to understand supported syntax.
    |
    */

    'enable_wildcard_permission' => false,

    /*
    |---------------------------------------------------------------------------
    | Wildcard Permission Class
    |---------------------------------------------------------------------------
    |
    | The class to use for interpreting wildcard permissions.
    | If you need to modify delimiters, override the class and specify its name here.
    |
    */
    // 'permission.wildcard_permission' => Spatie\Permission\WildcardPermission::class,

    'cache' => [

        /*
        |-----------------------------------------------------------------------
        | Cache Expiration Time
        |-----------------------------------------------------------------------
        |
        | By default all permissions are cached for 24 hours to speed up performance.
        | When permissions or roles are updated the cache is flushed automatically.
        |
        */

        'expiration_time' => DateInterval::createFromDateString('24 hours'),

        /*
        |-----------------------------------------------------------------------
        | Cache Key
        |-----------------------------------------------------------------------
        |
        | The cache key used to store all permissions.
        |
        */

        'key' => 'permission.cache',

        /*
        |-----------------------------------------------------------------------
        | Cache Store
        |-----------------------------------------------------------------------
        |
        | You may optionally indicate a specific cache driver to use for permission and
        | role caching using any of the `store` drivers listed in the cache.php config
        | file. Using 'default' here means to use the `default` set in cache.php.
        |
        */

        'store' => 'default',
    ],
];
