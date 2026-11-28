<?php

namespace Tests\Unit\Models;

use App\Models\CompetitionOrganizerType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompetitionOrganizerTypeTest extends TestCase
{
    use RefreshDatabase;

    public function test_weight_casts_to_integer_on_database_hydration(): void
    {
        $type = CompetitionOrganizerType::create([
            'name' => 'Ministry',
            'weight' => '5',
        ])->fresh();

        $this->assertSame(5, $type->weight);
    }
}
