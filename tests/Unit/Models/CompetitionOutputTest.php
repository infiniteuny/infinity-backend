<?php

namespace Tests\Unit\Models;

use App\Models\CompetitionOutput;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompetitionOutputTest extends TestCase
{
    use RefreshDatabase;

    public function test_weight_casts_to_integer_on_database_hydration(): void
    {
        $output = CompetitionOutput::create([
            'name' => 'Paper',
            'weight' => '4',
        ])->fresh();

        $this->assertSame(4, $output->weight);
    }
}
