<?php

namespace Tests\Unit\Http\Requests\CompetitionRank;

use App\Http\Requests\CompetitionOrganizerType\StoreCompetitionOrganizerTypeRequest;
use App\Http\Requests\CompetitionOrganizerType\UpdateCompetitionOrganizerTypeRequest;
use App\Http\Requests\CompetitionOutput\StoreCompetitionOutputRequest;
use App\Http\Requests\CompetitionOutput\UpdateCompetitionOutputRequest;
use App\Http\Requests\CompetitionRank\StoreCompetitionRankRequest;
use App\Http\Requests\CompetitionRank\UpdateCompetitionRankRequest;
use App\Http\Requests\CompetitionScale\StoreCompetitionScaleRequest;
use App\Http\Requests\CompetitionScale\UpdateCompetitionScaleRequest;
use App\Http\Requests\CompetitionTeamType\StoreCompetitionTeamTypeRequest;
use App\Http\Requests\CompetitionTeamType\UpdateCompetitionTeamTypeRequest;
use App\Http\Requests\CompetitionTimeRange\StoreCompetitionTimeRangeRequest;
use App\Http\Requests\CompetitionTimeRange\UpdateCompetitionTimeRangeRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class StoreCompetitionRankRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_competition_lookup_requests_enforce_weight_0_to_1000_and_name_max_255(): void
    {
        $storeClasses = [
            StoreCompetitionOrganizerTypeRequest::class,
            StoreCompetitionOutputRequest::class,
            StoreCompetitionRankRequest::class,
            StoreCompetitionScaleRequest::class,
            StoreCompetitionTeamTypeRequest::class,
            StoreCompetitionTimeRangeRequest::class,
        ];

        foreach ($storeClasses as $class) {
            $request = new $class;
            $rules = $request->rules();

            $this->assertTrue(Validator::make(['name' => str_repeat('a', 255), 'weight' => 0], $rules)->passes(), $class);
            $this->assertTrue(Validator::make(['name' => 'International', 'weight' => 1000], $rules)->passes(), $class);
            $this->assertFalse(Validator::make(['name' => 'International', 'weight' => -1], $rules)->passes(), $class);
            $this->assertFalse(Validator::make(['name' => 'International', 'weight' => 1001], $rules)->passes(), $class);
            $this->assertFalse(Validator::make(['name' => str_repeat('a', 256), 'weight' => 10], $rules)->passes(), $class);
        }

        $updateClasses = [
            UpdateCompetitionOrganizerTypeRequest::class,
            UpdateCompetitionOutputRequest::class,
            UpdateCompetitionRankRequest::class,
            UpdateCompetitionScaleRequest::class,
            UpdateCompetitionTeamTypeRequest::class,
            UpdateCompetitionTimeRangeRequest::class,
        ];

        foreach ($updateClasses as $class) {
            $request = new $class;
            $rules = $request->rules();

            $this->assertTrue(Validator::make(['name' => str_repeat('a', 255), 'weight' => 1000], $rules)->passes(), $class);
            $this->assertFalse(Validator::make(['weight' => -1], $rules)->passes(), $class);
            $this->assertFalse(Validator::make(['weight' => 1001], $rules)->passes(), $class);
            $this->assertFalse(Validator::make(['name' => str_repeat('a', 256)], $rules)->passes(), $class);
        }
    }
}
