<?php

declare(strict_types=1);

namespace PeibinLaravel\Utils\Providers;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Events\Dispatcher as IlluminateDispatcher;
use Illuminate\Support\ServiceProvider;
use SplPriorityQueue;

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
        $value = array_merge_recursive(config($key, []), $value);
        $this->app->get(Repository::class)->set($key, $value);
    }

    protected function registerDependencies(array $dependencies): void
    {
        foreach ($dependencies as $abstract => $concrete) {
            if (
                is_string($concrete) &&
                class_exists($concrete) &&
                method_exists($concrete, '__invoke')
            ) {
                $concrete = function () use ($concrete) {
                    return $this->app->call($concrete . '@__invoke');
                };
            }
            $this->app->singleton($abstract, $concrete);
        }
    }

    protected function registerCommands(array $commands): void
    {
        $this->commands($commands);
    }

    protected function registerListeners(array $listeners): void
    {
        // Support for prioritizing events.
        // Example: event_class=>[listener_class=>priority]
        // Example: event_class=>[listener_class]
        // Example: event_class=>listener_class

        $config = $this->app->get(Repository::class);
        $dispatcher = $this->app->get(Dispatcher::class);
        foreach ($listeners as $event => $group) {
            foreach ((array)$group as $listener => $priority) {
                if (is_int($listener)) {
                    $listener = $priority;
                    $priority = 0;
                }

                if (is_string($listener)) {
                    $dispatcher->listen($event, $listener);

                    $key = $this->getListenerConfigKey($event, $listener);
                    $config->set($key, $priority);
                    $this->resortListeners($dispatcher, $config, $event);
                }
            }
        }
    }

    protected function resortListeners(
        Dispatcher|IlluminateDispatcher $dispatcher,
        Repository $config,
        string $event
    ): void {
        $newListeners = new SplPriorityQueue();
        foreach ($dispatcher->getRawListeners()[$event] ?? [] as $listener) {
            $priority = is_string($listener)
                ? $config->get($this->getListenerConfigKey($event, $listener), 0)
                : PHP_INT_MAX;
            $newListeners->insert($listener, $priority);
        }

        $dispatcher->forget($event);
        foreach ($newListeners as $listener) {
            $dispatcher->listen($event, $listener);
        }
    }

    private function getListenerConfigKey(string $event, string $listener): string
    {
        return sprintf('listeners.%s.%s', $event, $listener);
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
