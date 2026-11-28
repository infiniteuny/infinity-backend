<?php

namespace Tests\Unit\Http\Requests\CoreTeam;

use App\Http\Requests\CoreTeam\UpdateCoreTeamRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use ReflectionMethod;
use Tests\TestCase;

class UpdateCoreTeamRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_enforces_year_bounds_and_boolean_is_active_on_partial_update(): void
    {
        $invalidYearRequest = UpdateCoreTeamRequest::create('/v1/core-teams/id', 'PATCH', [
            'year' => 2156,
        ]);
        (new ReflectionMethod($invalidYearRequest, 'prepareForValidation'))->invoke($invalidYearRequest);
        $this->assertTrue(Validator::make($invalidYearRequest->all(), $invalidYearRequest->rules())->fails());

        $validRequest = UpdateCoreTeamRequest::create('/v1/core-teams/id', 'PATCH', [
            'year' => 2155,
            'is_active' => 'true',
        ]);
        (new ReflectionMethod($validRequest, 'prepareForValidation'))->invoke($validRequest);
        $this->assertTrue(Validator::make($validRequest->all(), $validRequest->rules())->passes());
    }
}
