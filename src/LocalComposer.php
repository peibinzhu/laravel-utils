<?php

declare(strict_types=1);

namespace PeibinLaravel\Utils;

use Illuminate\Support\Collection;

class LocalComposer
{
    private static ?Collection $content = null;

    private static ?Collection $json = null;

    private static array $extra = [];

    private static array $scripts = [];

    private static array $versions = [];

    public static function getContent(): Collection
    {
        if (!self::$content) {
            $jsonContent = self::getJsonContent();
            $content = ['packages' => []];
            foreach ($jsonContent['autoload']['psr-4'] ?? [] as $path) {
                $composerFile = rtrim($path, '/') . '/../composer.json';
                if (
                    is_file($composerFile) &&
                    ($composerFile = realpath($composerFile)) !== base_path('composer.json')
                ) {
                    $composerFileContent = file_get_contents($composerFile);
                    $package = json_decode($composerFileContent, true);

                    $content['packages'][] = $package;

                    $packageName = '';
                    foreach ($package ?? [] as $key => $value) {
                        if ($key === 'name') {
                            $packageName = $value;
                            continue;
                        }
                        switch ($key) {
                            case 'extra':
                                $packageName && self::$extra[$packageName] = $value;
                                break;
                            case 'scripts':
                                $packageName && self::$scripts[$packageName] = $value;
                                break;
                            case 'version':
                                $packageName && self::$versions[$packageName] = $value;
                                break;
                        }
                    }
                }
            }

            self::$content = collect($content);
        }
        return self::$content;
    }

    public static function getJsonContent(): Collection
    {
        if (!self::$json) {
            $path = base_path('composer.json');
            if (!is_readable($path)) {
                throw new \RuntimeException('composer.json is not readable.');
            }
            self::$json = collect(json_decode(file_get_contents($path), true));
        }
        return self::$json;
    }

    public static function getMergedExtra(string $key = null): array
    {
        if (!self::$extra) {
            self::getContent();
        }
        if ($key === null) {
            return self::$extra;
        }

        $extra = [];
        foreach (self::$extra ?? [] as $config) {
            foreach ($config ?? [] as $configKey => $item) {
                if ($key === $configKey && $item) {
                    foreach ((array)$item as $k => $v) {
                        if (is_array($v)) {
                            $extra[$k] = array_merge($extra[$k] ?? [], $v);
                        } else {
                            $extra[$k][] = $v;
                        }
                    }
                }
            }
        }
        return $extra;
    }
}
