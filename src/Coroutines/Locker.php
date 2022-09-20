<?php

declare(strict_types=1);

namespace PeibinLaravel\Utils\Coroutines;

use PeibinLaravel\Utils\Traits\Container;
use Swoole\Coroutine;

class Locker
{
    use Container;

    public static function add(string $key, int $id): void
    {
        self::$container[$key][] = $id;
    }

    public static function clear(string $key): void
    {
        unset(self::$container[$key]);
    }

    public static function lock(string $key): bool
    {
        if (!self::has($key)) {
            self::add($key, 0);
            return true;
        }
        self::add($key, Coroutine::getCid());
        Coroutine::yield();
        return false;
    }

    public static function unlock(string $key): void
    {
        if (self::has($key)) {
            $ids = self::get($key);
            foreach ($ids as $id) {
                if ($id > 0) {
                    Coroutine::resume($id);
                }
            }
            self::clear($key);
        }
    }
}
