<?php

namespace App\Models;

use App\Traits\HasPermissions;
use BackedEnum;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Spatie\Permission\Contracts\Role as RoleContract;
use Spatie\Permission\Models\Role as Model;
use Spatie\Permission\PermissionRegistrar;

class Group extends Model implements RoleContract
{
    use HasFactory, HasPermissions, HasUuids;

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'guard_name' => 'api',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'sso_id',
        'sso_last_synced_at',
        'integration',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_managed' => 'boolean',
        'sso_last_synced_at' => 'datetime',
    ];

    public function __construct(array $attributes = [])
    {
        $attributes['guard_name'] ??= 'api';

        parent::__construct($attributes);
    }

    public static function create(array $attributes = [])
    {
        $attributes['guard_name'] ??= 'api';

        return parent::create($attributes);
    }

    public static function findByName(BackedEnum|string $name, ?string $guardName = null): RoleContract
    {
        return parent::findByName($name, $guardName ?? 'api');
    }

    public static function findById(int|string $id, ?string $guardName = null): RoleContract
    {
        return parent::findById($id, $guardName ?? 'api');
    }

    public static function findOrCreate(BackedEnum|string $name, ?string $guardName = null): RoleContract
    {
        return parent::findOrCreate($name, $guardName ?? 'api');
    }

    protected function getDefaultGuardName(): string
    {
        return $this->attributes['guard_name'] ?? 'api';
    }

    /**
     * Prepare a date for array / JSON serialization.
     */
    protected function serializeDate(DateTimeInterface $date): string
    {
        return $date->format(DATE_ATOM);
    }

    public function integration(): HasOne
    {
        return $this->hasOne(GroupIntegration::class, 'group_id');
    }

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
