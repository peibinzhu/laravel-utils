<?php

declare(strict_types=1);

namespace PeibinLaravel\Utils;

use Monolog\Formatter\FormatterInterface;
use PeibinLaravel\Contracts\StdoutLoggerInterface;
use Swoole\Coroutine as SwooleCo;
use Throwable;

class Coroutine
{
    /**
     * Returns the current coroutine ID.
     * Returns -1 when running in non-coroutine context.
     */
    public static function id(): int
    {
        return SwooleCo::getCid();
    }

    public static function defer(callable $callable): void
    {
        SwooleCo::defer(static function () use ($callable) {
            try {
                $callable();
            } catch (Throwable $throwable) {
                static::printLog($throwable);
            }
        });
    }

    public static function sleep(float $seconds): void
    {
        usleep(intval($seconds * 1000 * 1000));
    }

    /**
     * Returns the parent coroutine ID.
     * Returns 0 when running in the top level coroutine.
     * @param int|null $coroutineId
     * @return int
     */
    public static function parentId(?int $coroutineId = null): int
    {
        return SwooleCo::getPcid($coroutineId);
    }

    /**
     * @return int Returns the coroutine ID of the coroutine just created.
     *             Returns -1 when coroutine create failed.
     */
    public static function create(callable $callable): int
    {
        return SwooleCo::create(static function () use ($callable) {
            try {
                $callable();
            } catch (Throwable $throwable) {
                static::printLog($throwable);
            }
        });
    }

    public static function inCoroutine(): bool
    {
        return SwooleCo::getCid() > 0;
    }

    private static function printLog(Throwable $throwable): void
    {
        if (ApplicationContext::hasContainer()) {
            $container = ApplicationContext::getContainer();
            if ($container->has(StdoutLoggerInterface::class)) {
                $logger = $container->get(StdoutLoggerInterface::class);
                if ($container->has(FormatterInterface::class)) {
                    $formatter = $container->get(FormatterInterface::class);
                    $logger->warning($formatter->format($throwable));
                } else {
                    $logger->warning((string)$throwable);
                }
            }
        }
    }
}
