<?php

namespace Tests\Unit\Http\Requests\CommunityGroupAdmin;

use App\Http\Requests\CommunityGroupAdmin\UpdateCommunityGroupAdminRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
