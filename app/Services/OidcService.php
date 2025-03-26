<?php

namespace App\Services;

interface OidcService
{
    public function verify(string $token): array;

    public function introspect(string $token): array;

    public function getUserInfo(string $token): array;
}
