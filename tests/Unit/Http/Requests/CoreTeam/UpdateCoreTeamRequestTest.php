<?php

namespace Tests\Unit\Http\Requests\CoreTeam;

use App\Http\Requests\CoreTeam\UpdateCoreTeamRequest;
use App\Models\CoreTeam;
use App\Models\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
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

    public function test_rejects_colliding_group_name_while_ignoring_own_group(): void
    {
        $ownGroup = Group::create([
            'name' => 'Core Team 2028',
            'guard_name' => 'api',
            'is_managed' => true,
        ]);
        $coreTeam = CoreTeam::create([
            'year' => 2028,
            'group_id' => $ownGroup->id,
            'is_active' => false,
        ]);
        Group::create([
            'name' => 'Core Team 2030',
            'guard_name' => 'api',
            'is_managed' => false,
        ]);

        $makeValidator = static function (int $year) use ($coreTeam) {
            $request = UpdateCoreTeamRequest::create("/v1/core-teams/{$coreTeam->id}", 'PATCH', [
                'year' => $year,
            ]);
            $route = new Route('PATCH', '/v1/core-teams/{core_team}', []);
            $route->bind($request);
            $route->setParameter('core_team', $coreTeam);
            $request->setRouteResolver(static fn () => $route);
            (new ReflectionMethod($request, 'prepareForValidation'))->invoke($request);

            return Validator::make($request->all(), $request->rules());
        };

        $this->assertTrue($makeValidator(2028)->passes());

        $colliding = $makeValidator(2030);
        $this->assertTrue($colliding->fails());
        $this->assertSame(
            'The group name for this year is already taken.',
            $colliding->errors()->first('year'),
        );
    }
}
