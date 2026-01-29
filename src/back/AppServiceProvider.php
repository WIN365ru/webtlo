<?php

declare(strict_types=1);

namespace KeepersTeam\Webtlo;

use KeepersTeam\Webtlo\Cache\CacheFactory;
use KeepersTeam\Webtlo\Cache\CacheInterface;
use League\Container\ServiceProvider\AbstractServiceProvider;
use Psr\Log\LoggerInterface;

/**
 * Предоставляет ключевые классы для работы приложения.
 */
final class AppServiceProvider extends AbstractServiceProvider
{
    public function provides(string $id): bool
    {
        $services = [
            CacheInterface::class,
            TIniFileEx::class,
            WebTLO::class,
        ];

        return in_array($id, $services, true);
    }

    public function register(): void
    {
        $container = $this->getContainer();

        // Обработчик ini-файла с конфигом.
        $container->addShared(TIniFileEx::class, fn() => new TIniFileEx());

        // Подключаем описание версии WebTLO.
        $container->add(WebTLO::class, fn() => WebTLO::loadFromFile());

        // Подключаем кэш.
        $container->addShared(CacheInterface::class, function() use ($container) {
            return CacheFactory::create(
                ini   : $container->get(TIniFileEx::class),
                logger: $container->get(LoggerInterface::class),
            );
        });
    }
}
