<?php

namespace Tests\Unit\Models;

use App\Models\CommunityGroupAdmin;
use App\Models\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityGroupAdminTest extends TestCase
{
    use RefreshDatabase;

    public function test_casts_year_to_integer_and_is_active_to_boolean(): void
    {
        $group = Group::create(['name' => 'CG Admin 2025', 'is_managed' => true]);
        $cgAdmin = CommunityGroupAdmin::create([
            'year' => '2025',
            'is_active' => 0,
            'group_id' => $group->id,
        ])->fresh();

        $this->assertSame(2025, $cgAdmin->year);
        $this->assertSame(false, $cgAdmin->is_active);
    }
}
