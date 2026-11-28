<?php

namespace Tests\Unit\Utils;

use App\Utils\Encoder;
use PHPUnit\Framework\TestCase;

class EncoderTest extends TestCase
{
    public function test_encodes_validates_and_decodes_base64url_across_all_padding_remainders(): void
    {
        foreach (['a', 'ab', 'abc', 'abcd', '{"sub":"123","exp":1999999999}+/?='] as $payload) {
            $encoded = Encoder::base64UrlEncode($payload);
            $this->assertTrue(Encoder::isBase64Url($encoded));
            $this->assertSame($payload, Encoder::base64UrlDecode($encoded));
        }

        $this->assertFalse(Encoder::base64UrlDecode('a'));
        $this->assertFalse(Encoder::base64UrlDecode('invalid!chars'));
        $this->assertFalse(Encoder::isBase64Url('has+slash/'));
    }
}
