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
        $processes = ['dependencies', 'commands', 'processes', 'listeners', 'publish'];
        foreach ($processes as $key) {
            if ($config = $configs[$key] ?? []) {
                $method = 'register' . ucfirst($key);
                call_user_func([$this, $method], $config);
            }
        }
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

    protected function registerProcesses(array $processes): void
    {
        foreach ($processes as $process) {
            Config::push('processes', $process);
        }
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
