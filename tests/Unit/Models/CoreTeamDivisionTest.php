<?php

namespace Tests\Unit\Models;

use App\Models\CoreTeamDivision;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CoreTeamDivisionTest extends TestCase
{
    use RefreshDatabase;

    public function test_priority_casts_to_integer_on_database_hydration(): void
    {
        $division = CoreTeamDivision::create([
            'name' => 'Ops',
            'priority' => '2',
        ])->fresh();

        $this->assertSame(2, $division->priority);
    }
}
