<?php

declare(strict_types=1);

namespace PeibinLaravel\Utils;

use Psr\Container\ContainerInterface;
use TypeError;

class ApplicationContext
{
    private static ?ContainerInterface $container = null;

    /**
     * @throws TypeError
     */
    public static function getContainer(): ContainerInterface
    {
        return self::$container;
    }

    public static function hasContainer(): bool
    {
        return isset(self::$container);
    }

    public static function setContainer(ContainerInterface $container): ContainerInterface
    {
        self::$container = $container;
        return $container;
    }
}
