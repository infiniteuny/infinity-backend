<?php

namespace App\Utils;

class Encoder
{
    public static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    public static function base64UrlDecode(string $data): string|false
    {
        $remainder = strlen($data) % 4;

        if ($remainder === 1) {
            return false;
        }

        $padded = $remainder > 0
            ? str_pad(strtr($data, '-_', '+/'), strlen($data) + (4 - $remainder), '=', STR_PAD_RIGHT)
            : strtr($data, '-_', '+/');

        return base64_decode($padded, true);
    }

    public static function isBase64Url(string $data): bool
    {
        return preg_match('/^[A-Za-z0-9_-]+$/', $data) === 1;
    }
}
