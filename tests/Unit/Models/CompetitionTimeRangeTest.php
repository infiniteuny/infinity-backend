<?php

namespace Tests\Unit\Models;

use App\Models\CompetitionTimeRange;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompetitionTimeRangeTest extends TestCase
{
    use RefreshDatabase;

    public function test_weight_casts_to_integer_on_database_hydration(): void
    {
        $timeRange = CompetitionTimeRange::create([
            'name' => 'Semester',
            'weight' => '6',
        ])->fresh();

        $this->assertSame(6, $timeRange->weight);
    }
}
