<?php

namespace Tests\Unit\Models;

use App\Models\Group;
use Carbon\CarbonInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GroupTest extends TestCase
{
    use RefreshDatabase;

    public function test_defaults_guard_name_to_api_casts_attributes_and_serializes_atom_dates(): void
    {
        $group = Group::create([
            'name' => 'Test Group',
            'is_managed' => 1,
        ]);

        Group::withoutTimestamps(function () use ($group) {
            $group->sso_last_synced_at = '2026-06-01 12:00:00';
            $group->saveQuietly();
        });

        $group->refresh();

        $this->assertSame('api', $group->guard_name);
        $this->assertSame($group->id, Group::findByName('Test Group')->id);
        $this->assertTrue($group->is_managed);
        $this->assertInstanceOf(CarbonInterface::class, $group->sso_last_synced_at);
        $this->assertMatchesRegularExpression('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}[+-]\d{2}:\d{2}$/', $group->toArray()['created_at']);

        $factoryGroup = Group::factory()->create();
        $this->assertSame('api', $factoryGroup->guard_name);
    }
}
