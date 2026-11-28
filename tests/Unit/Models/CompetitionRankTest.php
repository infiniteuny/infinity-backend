<?php

namespace Tests\Unit\Models;

use App\Models\CompetitionRank;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompetitionRankTest extends TestCase
{
    use RefreshDatabase;

    public function test_weight_casts_to_integer_on_database_hydration(): void
    {
        $rank = CompetitionRank::create([
            'name' => 'Champion',
            'weight' => '10',
        ])->fresh();

        $this->assertSame(10, $rank->weight);
    }
}
