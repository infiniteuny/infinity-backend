<?php

namespace Tests\Unit\Models;

use App\Models\CompetitionScale;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompetitionScaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_weight_casts_to_integer_on_database_hydration(): void
    {
        $scale = CompetitionScale::create([
            'name' => 'International',
            'weight' => '8',
        ])->fresh();

        $this->assertSame(8, $scale->weight);
    }
}
