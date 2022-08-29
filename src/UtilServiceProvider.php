<?php

declare(strict_types=1);

namespace PeibinLaravel\Utils;

use Illuminate\Support\ServiceProvider;
use PeibinLaravel\Contracts\StdoutLoggerInterface;
use PeibinLaravel\Utils\Contracts\Formatter;
use PeibinLaravel\Utils\ExceptionHandler\DefaultFormatter;
use PeibinLaravel\Utils\Providers\RegisterProviderConfig;

class UtilServiceProvider extends ServiceProvider
{
    use RegisterProviderConfig;

    public function __invoke(): array
    {
        return [
            'dependencies' => [
                Formatter::class             => DefaultFormatter::class,
                StdoutLoggerInterface::class => StdoutLogger::class,
            ],
        ];
    }
}
