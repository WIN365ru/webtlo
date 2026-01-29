<?php

declare(strict_types=1);

namespace KeepersTeam\Webtlo\Cache;

use Redis;

final class RedisCache implements CacheInterface
{
    public function __construct(
        private readonly Redis $redis,
        private readonly string $prefix = 'webtlo:',
    ) {}

    public function get(string $key, mixed $default = null): mixed
    {
        $value = $this->redis->get($this->prefix . $key);
        if ($value === false) {
            return $default;
        }

        $decoded = @unserialize($value, ['allowed_classes' => false]);
        if ($decoded === false && $value !== serialize(false)) {
            return $default;
        }

        return $decoded;
    }

    public function set(string $key, mixed $value, int $ttlSeconds): bool
    {
        $payload = serialize($value);
        $prefixedKey = $this->prefix . $key;

        if ($ttlSeconds > 0) {
            return $this->redis->setex($prefixedKey, $ttlSeconds, $payload);
        }

        return $this->redis->set($prefixedKey, $payload);
    }

    public function delete(string $key): bool
    {
        return (bool) $this->redis->del($this->prefix . $key);
    }

    public function clear(): bool
    {
        $iterator = null;
        $keys = [];
        while ($scan = $this->redis->scan($iterator, $this->prefix . '*')) {
            $keys = array_merge($keys, $scan);
        }

        if (empty($keys)) {
            return true;
        }

        return (bool) $this->redis->del($keys);
    }
}
