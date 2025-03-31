<?php

namespace App\Models;

use App\Traits\HasPermissions;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Contracts\Role as RoleContract;
use Spatie\Permission\Models\Role as Model;
use Spatie\Permission\PermissionRegistrar;

class Group extends Model implements RoleContract
{
    use HasFactory, HasPermissions, HasUuids;

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            config('permission.table_names.role_has_permissions'),
            app(PermissionRegistrar::class)->pivotRole,
            config('permission.column_names.permission_pivot_key')
        )
            ->using(GroupPermission::class)
            ->as('entitlement')
            ->withPivot([
                'id',
            ]);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            config('permission.table_names.model_has_roles'),
            app(PermissionRegistrar::class)->pivotRole,
            config('permission.column_names.model_morph_key')
        )
            ->using(UserGroup::class)
            ->as('entitlement')
            ->withPivot([
                'id',
            ]);
    }
}
