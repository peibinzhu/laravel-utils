<?php

declare(strict_types=1);

namespace PeibinLaravel\Utils\Contracts;

use Throwable;

interface Formatter
{
    public function format(Throwable $throwable): string;
}
