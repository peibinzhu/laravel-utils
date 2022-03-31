<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace PeibinLaravel\Utils\Traits;

trait Container
{
    /**
     * @var array
     */
    protected static array $container = [];

    /**
     * Add a value to container by identifier.
     * @param string $id
     * @param mixed  $value
     */
    public static function set(string $id, mixed $value)
    {
        static::$container[$id] = $value;
    }

    /**
     * Finds an entry of the container by its identifier and returns it,
     * Retunrs $default when does not exists in the container.
     * @param mixed|null $default
     */
    public static function get(string $id, mixed $default = null)
    {
        return static::$container[$id] ?? $default;
    }

    /**
     * Returns true if the container can return an entry for the given identifier.
     * Returns false otherwise.
     */
    public static function has(string $id): bool
    {
        return isset(static::$container[$id]);
    }

    /**
     * Returns the container.
     */
    public static function list(): array
    {
        return static::$container;
    }

    /**
     * Clear the container.
     */
    public static function clear(): void
    {
        static::$container = [];
    }
}
