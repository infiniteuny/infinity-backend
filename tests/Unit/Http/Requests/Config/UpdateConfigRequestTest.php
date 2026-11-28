<?php

namespace Tests\Unit\Http\Requests\Config;

use App\Http\Requests\Config\UpdateConfigRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use ReflectionMethod;
use Tests\TestCase;

class UpdateConfigRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_rejects_null_blank_and_invalid_is_private_values_without_coercing_to_false(): void
    {
        foreach ([null, '', '   ', 'maybe', 2, []] as $invalid) {
            $request = UpdateConfigRequest::create('/v1/configs/allow_reregistration', 'PATCH', [
                'is_private' => $invalid,
            ]);
            (new ReflectionMethod($request, 'prepareForValidation'))->invoke($request);

            $this->assertNotSame(false, $request->input('is_private'));
            $this->assertTrue(Validator::make($request->all(), $request->rules())->fails());
        }

        $validRequest = UpdateConfigRequest::create('/v1/configs/allow_reregistration', 'PATCH', [
            'is_private' => 'false',
        ]);
        (new ReflectionMethod($validRequest, 'prepareForValidation'))->invoke($validRequest);

        $this->assertSame(false, $validRequest->input('is_private'));
        $this->assertTrue(Validator::make($validRequest->all(), $validRequest->rules())->passes());
    }
}
