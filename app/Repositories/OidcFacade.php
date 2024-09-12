<?php

namespace App\Repositories;

interface OidcFacade
{
    public function verify(string $token): array;

    public function introspect(string $token): array;

    public function getUserInfo(string $token): array;
}
