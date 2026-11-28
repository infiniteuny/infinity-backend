<?php

namespace Tests\Unit\Http\Requests\CoreTeam;

use App\Http\Requests\CoreTeam\StoreCoreTeamRequest;
use App\Models\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use ReflectionMethod;
use Tests\TestCase;

class StoreCoreTeamRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_bounds_year_to_1901_through_2155_and_skips_unique_query_when_invalid(): void
    {
        foreach ([1900, 2156, 999999999999, -2025, 'not-a-year'] as $invalidYear) {
            DB::enableQueryLog();
            DB::flushQueryLog();

            $request = StoreCoreTeamRequest::create('/v1/core-teams', 'POST', [
                'year' => $invalidYear,
                'is_active' => 'false',
            ]);
            (new ReflectionMethod($request, 'prepareForValidation'))->invoke($request);

            $this->assertTrue(Validator::make($request->all(), $request->rules())->fails());
            $this->assertCount(0, DB::getQueryLog());
            DB::disableQueryLog();
        }

        $validRequest = StoreCoreTeamRequest::create('/v1/core-teams', 'POST', [
            'year' => 2026,
            'is_active' => 'true',
        ]);
        (new ReflectionMethod($validRequest, 'prepareForValidation'))->invoke($validRequest);

        $this->assertTrue(Validator::make($validRequest->all(), $validRequest->rules())->passes());
    }

    public function test_rejects_year_when_group_name_is_already_occupied(): void
    {
        Group::create([
            'name' => 'Core Team 2030',
            'guard_name' => 'api',
            'is_managed' => false,
        ]);

        $request = StoreCoreTeamRequest::create('/v1/core-teams', 'POST', [
            'year' => 2030,
            'is_active' => 'false',
        ]);
        (new ReflectionMethod($request, 'prepareForValidation'))->invoke($request);

        $validator = Validator::make($request->all(), $request->rules());
        $this->assertTrue($validator->fails());
        $this->assertSame(
            'The group name for this year is already taken.',
            $validator->errors()->first('year'),
        );
    }
}
