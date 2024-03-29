<?php

declare(strict_types=1);

namespace PeibinLaravel\Utils\Codec;

use InvalidArgumentException;
use Throwable;

class Json
{
    public static function encode(mixed $data, int $flags = JSON_UNESCAPED_UNICODE, int $depth = 512): string
    {
        try {
            $json = json_encode($data, $flags | JSON_THROW_ON_ERROR, $depth);
        } catch (Throwable $exception) {
            throw new InvalidArgumentException($exception->getMessage(), $exception->getCode());
        }
        return $json;
    }

    public static function decode(string $json, bool $assoc = true, int $depth = 512, int $flags = 0): mixed
    {
        try {
            $decode = json_decode($json, $assoc, $depth, $flags | JSON_THROW_ON_ERROR);
        } catch (Throwable $exception) {
            throw new InvalidArgumentException($exception->getMessage(), $exception->getCode());
        }

        return $decode;
    }
}
