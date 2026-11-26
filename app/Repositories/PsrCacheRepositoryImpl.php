<?php

namespace App\Repositories;

use DateInterval;
use Illuminate\Cache\Repository;

class PsrCacheRepositoryImpl implements PsrCacheRepository
{
    public function __construct(
        private Repository $cacheRepository,
    ) {}

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->cacheRepository->get($key, $default);
    }

    public function set(string $key, mixed $value, null|int|DateInterval $ttl = null): bool
    {
        return $this->cacheRepository->set($key, $value, $ttl);
    }

    public function delete(string $key): bool
    {
        return $this->cacheRepository->delete($key);
    }

    public function clear(): bool
    {
        return $this->cacheRepository->clear();
    }

    public function getMultiple(iterable $keys, mixed $default = null): iterable
    {
        return $this->cacheRepository->getMultiple($keys, $default);
    }

    public function setMultiple(iterable $values, null|int|DateInterval $ttl = null): bool
    {
        return $this->cacheRepository->setMultiple($values, $ttl);
    }

    public function deleteMultiple(iterable $keys): bool
    {
        return $this->cacheRepository->deleteMultiple($keys);
    }

    public function has(string $key): bool
    {
        return $this->cacheRepository->has($key);
    }
}
