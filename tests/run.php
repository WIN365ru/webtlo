<?php

declare(strict_types=1);

use KeepersTeam\Webtlo\Cache\CacheFactory;
use KeepersTeam\Webtlo\Cache\NullCache;
use KeepersTeam\Webtlo\TIniFileEx;
use Psr\Log\NullLogger;

$autoload = __DIR__ . '/../src/vendor/autoload.php';
if (!file_exists($autoload)) {
    fwrite(STDERR, "Autoload not found. Run 'cd src && composer install' first." . PHP_EOL);
    exit(1);
}

require $autoload;

function assertSame(mixed $expected, mixed $actual, string $message): void
{
    if ($expected !== $actual) {
        fwrite(STDERR, "Assertion failed: {$message}. Expected " . var_export($expected, true) . ' got ' . var_export($actual, true) . PHP_EOL);
        exit(1);
    }
}

$tmpDir = __DIR__ . '/tmp';
if (!is_dir($tmpDir)) {
    mkdir($tmpDir, 0777, true);
}

putenv('WEBTLO_DIR=' . $tmpDir);

$configPath = $tmpDir . '/config.ini';
file_put_contents($configPath, "[redis]\nenabled=0\n");

$ini = new TIniFileEx('config.ini');
$cache = CacheFactory::create($ini, new NullLogger());

assertSame(true, $cache instanceof NullCache, 'CacheFactory should return NullCache when redis is disabled');
assertSame('default', $cache->get('missing', 'default'), 'NullCache should return default value');
assertSame(true, $cache->set('key', 'value', 60), 'NullCache set should return true');
assertSame(true, $cache->delete('key'), 'NullCache delete should return true');
assertSame(true, $cache->clear(), 'NullCache clear should return true');

fwrite(STDOUT, "OK\n");
