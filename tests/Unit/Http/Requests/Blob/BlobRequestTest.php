<?php

namespace Tests\Unit\Http\Requests\Blob;

use App\Http\Requests\Blob\BlobRequest;
use Illuminate\Support\Facades\Validator;
use ReflectionMethod;
use Tests\TestCase;

class BlobRequestTest extends TestCase
{
    public function test_coerces_valid_force_download_boolean_and_rejects_blank_or_invalid_values(): void
    {
        $validate = static function (array $query): bool {
            $request = BlobRequest::create('/static/private/doc.pdf', 'GET', $query);
            (new ReflectionMethod($request, 'prepareForValidation'))->invoke($request);

            return Validator::make($request->all(), $request->rules())->passes();
        };

        $this->assertTrue($validate([]));
        $this->assertTrue($validate(['force_download' => 'true']));
        $this->assertTrue($validate(['force_download' => 'false']));
        $this->assertFalse($validate(['force_download' => '']));
        $this->assertFalse($validate(['force_download' => 'maybe']));
    }
}
