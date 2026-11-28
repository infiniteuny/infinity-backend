<?php

namespace Tests\Unit\Rules;

use App\Rules\Associative;
use Illuminate\Support\Facades\Validator;
use Tests\TestCase;

class AssociativeTest extends TestCase
{
    public function test_accepts_empty_and_keyed_arrays_while_rejecting_lists_and_scalars(): void
    {
        $rule = ['links' => [new Associative]];

        $this->assertFalse(Validator::make(['links' => []], $rule)->fails());
        $this->assertFalse(Validator::make(['links' => ['github' => 'https://github.com/infiniteuny']], $rule)->fails());
        $this->assertTrue(Validator::make(['links' => ['https://github.com/infiniteuny']], $rule)->fails());
        $this->assertTrue(Validator::make(['links' => 'not-an-array'], $rule)->fails());
    }
}
