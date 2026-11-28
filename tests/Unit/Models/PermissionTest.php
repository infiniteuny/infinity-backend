<?php

namespace Tests\Unit\Models;

use App\Models\Permission;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_defaults_guard_name_to_api_and_serializes_atom_dates(): void
    {
        $permission = Permission::create(['name' => 'test-permission']);

        $this->assertSame('api', $permission->guard_name);
        $this->assertSame($permission->id, Permission::findByName('test-permission')->id);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/', $permission->toArray()['created_at']);

        $factoryPermission = Permission::factory()->create();
        $this->assertSame('api', $factoryPermission->guard_name);
    }
}
