<?php

declare(strict_types=1);

namespace PeibinLaravel\Utils\CodeGen;

use PhpDocReader\PhpDocReader;

class PhpDocReaderManager
{
    /**
     * @var null|PhpDocReader
     */
    protected static $instance;

    public static function getInstance(): PhpDocReader
    {
        if (static::$instance) {
            return static::$instance;
        }
        return static::$instance = new PhpDocReader();
    }
}
