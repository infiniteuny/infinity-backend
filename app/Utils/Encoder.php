<?php

namespace App\Utils;

class Encoder
{
    public static function base64UrlEncode(string $data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public static function base64UrlDecode(string $data)
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
    }

    public static function isBase64Url(string $data)
    {
        return preg_match('/^[A-Za-z0-9_-]+$/', $data) === 1;
    }
}
