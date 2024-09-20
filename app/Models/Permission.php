<?php

namespace App\Models;

use App\Traits\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Contracts\Permission as PermissionContract;
use Spatie\Permission\Models\Permission as Model;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Traits\HasRoles;

class Permission extends Model implements PermissionContract
{
    use HasFactory, HasUuids;
    use HasRoles;

    public function groups(): BelongsToMany
    {
        return $this->roles();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            config('permission.table_names.model_has_permissions'),
            app(PermissionRegistrar::class)->pivotPermission,
            config('permission.column_names.model_morph_key')
        );
    }
}
