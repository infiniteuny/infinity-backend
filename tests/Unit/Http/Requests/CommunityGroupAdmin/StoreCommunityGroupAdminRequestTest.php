<?php

namespace Tests\Unit\Http\Requests\CommunityGroupAdmin;

use App\Http\Requests\CommunityGroupAdmin\StoreCommunityGroupAdminRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use ReflectionMethod;
use Tests\TestCase;

class StoreCommunityGroupAdminRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_bounds_year_to_1901_through_2155_and_skips_unique_query_when_invalid(): void
    {
        foreach ([1900, 2156, 999999999999, 'not-a-year'] as $invalidYear) {
            DB::enableQueryLog();
            DB::flushQueryLog();

            $request = StoreCommunityGroupAdminRequest::create('/v1/community-group-admins', 'POST', [
                'year' => $invalidYear,
                'is_active' => 'false',
            ]);
            (new ReflectionMethod($request, 'prepareForValidation'))->invoke($request);

            $this->assertTrue(Validator::make($request->all(), $request->rules())->fails());
            $this->assertCount(0, DB::getQueryLog());
            DB::disableQueryLog();
        }

        $validRequest = StoreCommunityGroupAdminRequest::create('/v1/community-group-admins', 'POST', [
            'year' => 1901,
            'is_active' => 'false',
        ]);
        (new ReflectionMethod($validRequest, 'prepareForValidation'))->invoke($validRequest);

        $this->assertTrue(Validator::make($validRequest->all(), $validRequest->rules())->passes());
    }
}
