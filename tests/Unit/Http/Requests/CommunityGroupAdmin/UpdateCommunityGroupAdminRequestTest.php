<?php

namespace Tests\Unit\Http\Requests\CommunityGroupAdmin;

use App\Http\Requests\CommunityGroupAdmin\UpdateCommunityGroupAdminRequest;
use App\Models\CommunityGroupAdmin;
use App\Models\Group;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Validator;
use ReflectionMethod;
use Tests\TestCase;

class UpdateCommunityGroupAdminRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_enforces_year_bounds_and_boolean_is_active_on_partial_update(): void
    {
        $invalidRequest = UpdateCommunityGroupAdminRequest::create('/v1/community-group-admins/id', 'PATCH', [
            'year' => 1800,
        ]);
        (new ReflectionMethod($invalidRequest, 'prepareForValidation'))->invoke($invalidRequest);
        $this->assertTrue(Validator::make($invalidRequest->all(), $invalidRequest->rules())->fails());

        $validRequest = UpdateCommunityGroupAdminRequest::create('/v1/community-group-admins/id', 'PATCH', [
            'year' => 2027,
            'is_active' => 'true',
        ]);
        (new ReflectionMethod($validRequest, 'prepareForValidation'))->invoke($validRequest);
        $this->assertTrue(Validator::make($validRequest->all(), $validRequest->rules())->passes());
    }

    public function test_rejects_colliding_group_name_while_ignoring_own_group(): void
    {
        $ownGroup = Group::create([
            'name' => 'CG Admin 2028',
            'guard_name' => 'api',
            'is_managed' => true,
        ]);
        $admin = CommunityGroupAdmin::create([
            'year' => 2028,
            'group_id' => $ownGroup->id,
            'is_active' => false,
        ]);
        Group::create([
            'name' => 'CG Admin 2030',
            'guard_name' => 'api',
            'is_managed' => false,
        ]);

        $makeValidator = static function (int $year) use ($admin) {
            $request = UpdateCommunityGroupAdminRequest::create("/v1/community-group-admins/{$admin->id}", 'PATCH', [
                'year' => $year,
            ]);
            $route = new Route('PATCH', '/v1/community-group-admins/{community_group_admin}', []);
            $route->bind($request);
            $route->setParameter('community_group_admin', $admin);
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
