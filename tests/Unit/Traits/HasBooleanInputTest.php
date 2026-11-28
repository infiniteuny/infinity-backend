<?php

namespace Tests\Unit\Traits;

use App\Traits\HasBooleanInput;
use Illuminate\Foundation\Http\FormRequest;
use Tests\TestCase;

class HasBooleanInputTest extends TestCase
{
    public function test_coerces_valid_boolean_inputs_and_preserves_null_or_invalid_values(): void
    {
        $makeRequest = static function (array $data): FormRequest {
            $request = new class extends FormRequest
            {
                use HasBooleanInput;

                public function exposePrepare(array $fields): void
                {
                    $this->prepareBooleanInput($fields);
                }
            };

            $request->replace($data);
            $request->exposePrepare(['flag']);

            return $request;
        };

        $this->assertSame(true, $makeRequest(['flag' => 'true'])->input('flag'));
        $this->assertSame(false, $makeRequest(['flag' => 'false'])->input('flag'));
        $this->assertSame(true, $makeRequest(['flag' => 1])->input('flag'));
        $this->assertSame(false, $makeRequest(['flag' => 0])->input('flag'));

        // Blank strings normalize to null (never false); null and invalid inputs remain un-coerced
        $this->assertNull($makeRequest(['flag' => ''])->input('flag'));
        $this->assertNull($makeRequest(['flag' => '   '])->input('flag'));
        $this->assertNull($makeRequest(['flag' => null])->input('flag'));
        $this->assertSame('maybe', $makeRequest(['flag' => 'maybe'])->input('flag'));
        $this->assertSame(2, $makeRequest(['flag' => 2])->input('flag'));
    }
}
