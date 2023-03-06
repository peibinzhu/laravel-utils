<?php

declare(strict_types=1);

namespace PeibinLaravel\Utils;

use Illuminate\Support\ServiceProvider;
use PeibinLaravel\Contracts\StdoutLoggerInterface;
use PeibinLaravel\ProviderConfig\Contracts\ProviderConfigInterface;
use PeibinLaravel\Utils\Contracts\Formatter;
use PeibinLaravel\Utils\ExceptionHandler\DefaultFormatter;

class UtilServiceProvider extends ServiceProvider implements ProviderConfigInterface
{
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
