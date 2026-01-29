<?php

declare(strict_types=1);

namespace KeepersTeam\Webtlo\Cache;

use KeepersTeam\Webtlo\TIniFileEx;
use Psr\Log\LoggerInterface;
use Redis;
use Throwable;

final class CacheFactory
{
    public static function create(TIniFileEx $ini, LoggerInterface $logger): CacheInterface
    {
        $enabled = (bool) $ini->read('redis', 'enabled', 0);
        if (!$enabled) {
            return new NullCache();
        }

        if (!class_exists(Redis::class)) {
            $logger->notice('Redis extension is not available. Cache is disabled.');

            return new NullCache();
        }

        $host    = (string) $ini->read('redis', 'host', '127.0.0.1');
        $port    = (int) $ini->read('redis', 'port', 6379);
        $timeout = (float) $ini->read('redis', 'timeout', 1.0);
        $auth    = (string) $ini->read('redis', 'password', '');
        $db      = (int) $ini->read('redis', 'database', 0);
        $prefix  = (string) $ini->read('redis', 'prefix', 'webtlo:');

        try {
            $redis = new Redis();
            $redis->connect($host, $port, $timeout);

            if ($auth !== '') {
                $redis->auth($auth);
            }

            if ($db > 0) {
                $redis->select($db);
            }

            $logger->info('Redis cache enabled.', ['host' => $host, 'port' => $port, 'db' => $db]);

            return new RedisCache($redis, $prefix);
        } catch (Throwable $e) {
            $logger->warning('Redis cache disabled due to connection error.', ['error' => $e->getMessage()]);
        }

        return new NullCache();
    }
}
