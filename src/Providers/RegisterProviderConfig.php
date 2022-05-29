<?php

declare(strict_types=1);

namespace PeibinLaravel\Utils\Providers;

use Illuminate\Console\Application;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

/**
 * @mixin ServiceProvider
 */
trait RegisterProviderConfig
{
    public function boot()
    {
        $this->registerConfigs($this->__invoke());
    }

    protected function registerConfigs(array $configs): void
    {
        $specials = ['dependencies', 'commands', 'listeners', 'publish'];
        $specials = array_flip($specials);
        foreach ($configs as $key => $config) {
            if (isset($specials[$key])) {
                $method = 'register' . ucfirst($key);
                call_user_func([$this, $method], $config);
            } else {
                call_user_func([$this, 'registerConfig'], $key, $config);
            }
        }
    }

    protected function registerConfig(string $key, array $value): void
    {
        Config::set($key, array_merge_recursive(config($key, []), $value));
    }

    protected function registerDependencies(array $dependencies): void
    {
        foreach ($dependencies as $abstract => $concrete) {
            if (method_exists($concrete, '__invoke')) {
                $concrete = function ($app, $parameters) use ($concrete) {
                    return $app->make($concrete, $parameters)();
                };
            }
            $this->app->bind($abstract, $concrete);
        }
    }

    protected function registerCommands(array $commands): void
    {
        $this->app->make(Application::class)->resolveCommands($commands);
    }

    protected function registerListeners(array $listeners): void
    {
        foreach ($listeners as $event => $group) {
            $group = (array)$group;
            foreach ($group as $listener) {
                Event::listen($event, $listener);
            }
        }
    }

    protected function registerPublish(array $publish): void
    {
        if ($this->app->runningInConsole()) {
            foreach ($publish as $item) {
                $this->publishes(
                    [$item['source'] => $item['destination']],
                    $item['id']
                );
            }
        }
    }
}
