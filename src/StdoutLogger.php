<?php

declare(strict_types=1);

namespace PeibinLaravel\Utils;

use Illuminate\Contracts\Config\Repository;
use Illuminate\Contracts\Container\Container;
use Laravel\Octane\Swoole\WorkerState;
use PeibinLaravel\Contracts\StdoutLoggerInterface;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;
use function str_replace;

/**
 * Default logger for logging server start and requests.
 * PSR-3 logger implementation that logs to STDOUT, using a newline after each
 * message. Priority is ignored.
 */
class StdoutLogger implements StdoutLoggerInterface
{
    private OutputInterface $output;

    private array $tags = [
        'component',
    ];

    public function __construct(
        protected Container $container,
        protected Repository $config,
        ?OutputInterface $output = null
    ) {
        $this->output = $output ?? new ConsoleOutput();
    }

    public function emergency($message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    public function alert($message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    public function critical($message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    public function error($message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    public function warning($message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    public function notice($message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    public function info($message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    public function debug($message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    public function log($level, $message, array $context = []): void
    {
        $config = $this->config->get('logging.' . StdoutLoggerInterface::class, ['log_level' => []]);
        if (!in_array($level, $config['log_level'], true)) {
            return;
        }
        $keys = array_keys($context);
        $tags = [];
        foreach ($keys as $k => $key) {
            if (in_array($key, $this->tags, true)) {
                $tags[$key] = $context[$key];
                unset($keys[$k]);
            }
        }
        $search = array_map(function ($key) {
            return sprintf('{%s}', $key);
        }, $keys);
        $message = str_replace($search, $context, $this->getMessage((string)$message, $level, $tags));

        // Output raw json content when octane.
        if ($this->container->has(WorkerState::class)) {
            $message = json_encode(['type' => 'raw', 'message' => urlencode($message)]);
        }

        $this->output->writeln($message);
    }

    protected function getMessage(string $message, string $level = LogLevel::INFO, array $tags = []): string
    {
        $tag = match ($level) {
            LogLevel::EMERGENCY, LogLevel::ALERT, LogLevel::CRITICAL => 'error',
            LogLevel::ERROR => 'fg=red',
            LogLevel::WARNING, LogLevel::NOTICE => 'comment',
            default => 'info',
        };

        $template = sprintf('<%s>[%s]</>', $tag, strtoupper($level));
        $implodedTags = '';
        foreach ($tags as $value) {
            $implodedTags .= (' [' . $value . ']');
        }

        return sprintf($template . $implodedTags . ' %s', $message);
    }
}
