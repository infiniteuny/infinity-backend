<?php

namespace App\Models;

use App\Traits\HasGroups;
use BackedEnum;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Contracts\Permission as PermissionContract;
use Spatie\Permission\Models\Permission as Model;
use Spatie\Permission\PermissionRegistrar;

class Permission extends Model implements PermissionContract
{
    use HasFactory, HasGroups, HasUuids;

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'guard_name' => 'api',
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

    public static function findByName(BackedEnum|string $name, ?string $guardName = null): PermissionContract
    {
        return parent::findByName($name, $guardName ?? 'api');
    }

    public static function findById(int|string $id, ?string $guardName = null): PermissionContract
    {
        return parent::findById($id, $guardName ?? 'api');
    }

    public static function findOrCreate(BackedEnum|string $name, ?string $guardName = null): PermissionContract
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

    public function groups(): BelongsToMany
    {
        return $this->belongsToMany(
            Group::class,
            config('permission.table_names.role_has_permissions'),
            app(PermissionRegistrar::class)->pivotPermission,
            config('permission.column_names.role_pivot_key')
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
            config('permission.table_names.model_has_permissions'),
            app(PermissionRegistrar::class)->pivotPermission,
            config('permission.column_names.model_morph_key')
        )
            ->using(UserPermission::class)
            ->as('entitlement')
            ->withPivot([
                'id',
            ]);
    }
}
