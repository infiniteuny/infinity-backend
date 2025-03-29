<?php

namespace App\Traits;

use App\Models\UserGroup;
use BackedEnum;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;
use Spatie\Permission\Contracts\Role;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Traits\HasRoles as TraitsHasRoles;

trait HasGroups
{
    use HasPermissions, TraitsHasRoles {
        HasPermissions::permissions insteadof TraitsHasRoles;
    }

    public function groups(): BelongsToMany
    {
        $relation = $this->belongsToMany(
            config('permission.models.role'),
            config('permission.table_names.model_has_roles'),
            config('permission.column_names.model_morph_key'),
            app(PermissionRegistrar::class)->pivotRole
        )
            ->using(UserGroup::class)
            ->as('entitlement')
            ->withPivot([
                'id',
            ]);

        if (! app(PermissionRegistrar::class)->teams) {
            return $relation;
        }

        $teamsKey = app(PermissionRegistrar::class)->teamsKey;
        $relation->withPivot($teamsKey);
        $teamField = config('permission.table_names.roles').'.'.$teamsKey;

        return $relation->wherePivot($teamsKey, getPermissionsTeamId())
            ->where(fn ($q) => $q->whereNull($teamField)->orWhere($teamField, getPermissionsTeamId()));
    }

    public function roles(): BelongsToMany
    {
        return $this->groups();
    }

    public function scopeGroup(Builder $query, $roles, $guard = null, $without = false): Builder
    {
        return $this->scopeRole($query, $roles, $guard, $without);
    }

    public function scopeWithoutGroup(Builder $query, string|int|array|Role|Collection|\BackedEnum $roles, ?string $guard = null): Builder
    {
        return $this->scopeGroup($query, $roles, $guard, true);
    }

    public function assignGroup(...$roles)
    {
        return $this->assignRole(...$roles);
    }

    public function removeGroup(string|int|Role|BackedEnum $role)
    {
        return $this->removeRole($role);
    }

    public function syncGroups(...$roles)
    {
        return $this->syncRoles(...$roles);
    }

    public function hasGroup(string|int|array|Role|Collection|BackedEnum $roles, ?string $guard = null): bool
    {
        return $this->hasRole($roles, $guard);
    }

    public function hasAnyGroup(...$roles): bool
    {
        return $this->hasAnyRole(...$roles);
    }

    public function hasAllGroups(string|array|Role|Collection|BackedEnum $roles, ?string $guard = null): bool
    {
        return $this->hasAllRoles($roles, $guard);
    }

    public function hasExactGroups(string|array|Role|Collection|BackedEnum $roles, ?string $guard = null): bool
    {
        return $this->hasExactRoles($roles, $guard);
    }

    public function getGroupNames(): Collection
    {
        return $this->getRoleNames();
    }
}
