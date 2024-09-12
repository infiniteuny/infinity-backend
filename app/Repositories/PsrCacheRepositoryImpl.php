<?php

namespace App\Repositories;

use Illuminate\Support\Facades\Cache;

class PsrCacheRepositoryImpl implements PsrCacheRepository
{
    public function get($key, $default = null): mixed
    {
        return Cache::get($key, $default);
    }

    public function set($key, $value, $ttl = null): bool
    {
        Cache::put($key, $value, $this->ttl2minutes($ttl));

        return true;
    }

    public function delete($key): bool
    {
        return Cache::forget($key);
    }

    public function clear(): bool
    {
        return Cache::flush();
    }

    public function getMultiple($keys, $default = null): iterable
    {
        return Cache::many($keys);
    }

    public function setMultiple($values, $ttl = null): bool
    {
        Cache::putMany((array) $values, $this->ttl2minutes($ttl));

        return true;
    }

    public function deleteMultiple($keys): bool
    {
        foreach ($keys as $key) {
            $this->delete($key);
        }

        return true;
    }

    public function has($key): bool
    {
        return Cache::has($key);
    }

    protected function ttl2minutes($ttl): float|int|null
    {
        if (is_null($ttl)) {
            return null;
        }

        if ($ttl instanceof \DateInterval) {
            return $ttl->days * 86400 + $ttl->h * 3600 + $ttl->i * 60;
        }

        return $ttl / 60;
    }
}
