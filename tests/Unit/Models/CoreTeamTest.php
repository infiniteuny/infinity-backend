<?php

namespace Tests\Unit\Models;

use App\Models\CoreTeam;
use App\Models\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoreTeamTest extends TestCase
{
    use RefreshDatabase;

    public function test_casts_year_to_integer_and_is_active_to_boolean(): void
    {
        $group = Group::create(['name' => 'Core Team 2025', 'is_managed' => true]);
        $coreTeam = CoreTeam::create([
            'year' => '2025',
            'is_active' => 1,
            'group_id' => $group->id,
        ])->fresh();

        $this->assertSame(2025, $coreTeam->year);
        $this->assertSame(true, $coreTeam->is_active);
    }
}
