<?php

namespace App\Repositories;

use Illuminate\Cache\Repository;

class PsrCacheRepositoryImpl implements PsrCacheRepository
{
    public function __construct(
        private Repository $cacheRepository,
    ) {}

    public function get($key, $default = null): mixed
    {
        return $this->cacheRepository->get($key, $default);
    }

    public function set($key, $value, $ttl = null): bool
    {
        $this->cacheRepository->put($key, $value, $this->ttl2minutes($ttl));

        return true;
    }

    public function delete($key): bool
    {
        return $this->cacheRepository->forget($key);
    }

    public function clear(): bool
    {
        return $this->cacheRepository->flush();
    }

    public function getMultiple($keys, $default = null): iterable
    {
        return $this->cacheRepository->many($keys);
    }

    public function setMultiple($values, $ttl = null): bool
    {
        $this->cacheRepository->putMany((array) $values, $this->ttl2minutes($ttl));

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
        return $this->cacheRepository->has($key);
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
