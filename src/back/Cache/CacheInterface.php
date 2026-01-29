<?php

declare(strict_types=1);

namespace KeepersTeam\Webtlo\Cache;

interface CacheInterface
{
    public function get(string $key, mixed $default = null): mixed;

    public function set(string $key, mixed $value, int $ttlSeconds): bool;

    public function delete(string $key): bool;

    public function clear(): bool;
}
