<?php

namespace Tests\Unit\Http\Requests\Config;

use App\Http\Requests\Config\StoreConfigRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Validator;
use ReflectionMethod;
use Tests\TestCase;

class StoreConfigRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_requires_valid_boolean_is_private_and_rejects_null_or_invalid_strings(): void
    {
        $makeValidator = static function (mixed $isPrivate) {
            $request = StoreConfigRequest::create('/v1/configs', 'POST', [
                'key' => 'flag_key',
                'value' => '1',
                'type' => 'BOOLEAN',
                'is_private' => $isPrivate,
            ]);
            (new ReflectionMethod($request, 'prepareForValidation'))->invoke($request);

            return Validator::make($request->all(), $request->rules());
        };

        $this->assertTrue($makeValidator('true')->passes());
        $this->assertTrue($makeValidator(0)->passes());
        $this->assertTrue($makeValidator(null)->fails());

        $invalidValidator = $makeValidator('not-a-bool');
        $this->assertTrue($invalidValidator->fails());
        $this->assertStringContainsString('true or false', $invalidValidator->errors()->first('is_private'));
    }
}
