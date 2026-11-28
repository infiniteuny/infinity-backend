<?php

namespace Tests\Unit\Models;

use App\Models\CompetitionTeamType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompetitionTeamTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_weight_casts_to_integer_on_database_hydration(): void
    {
        $teamType = CompetitionTeamType::create([
            'name' => 'Duo',
            'weight' => '3',
        ])->fresh();

        $this->assertSame(3, $teamType->weight);
    }
}
