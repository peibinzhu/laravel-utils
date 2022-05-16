<?php

declare(strict_types=1);

namespace PeibinLaravel\Utils\ExceptionHandler;

use PeibinLaravel\Utils\Contracts\Formatter;
use Throwable;

class DefaultFormatter implements Formatter
{
    public function format(Throwable $throwable): string
    {
        return sprintf(
            "%s: %s(%s) in %s:%s\nStack trace:\n%s",
            get_class($throwable),
            $throwable->getMessage(),
            $throwable->getCode(),
            $throwable->getFile(),
            $throwable->getLine(),
            $throwable->getTraceAsString()
        );
    }
}
