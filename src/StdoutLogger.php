<?php

declare(strict_types=1);

namespace PeibinLaravel\Utils;

use Laravel\Octane\Swoole\WorkerState;
use PeibinLaravel\Utils\Contracts\StdoutLogger as StdoutLoggerContract;
use Psr\Log\LogLevel;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;
use function str_replace;

class StdoutLogger implements StdoutLoggerContract
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @var array
     */
    private $tags = [
        'component',
    ];

    public function __construct($output = null)
    {
        $this->output = $output ?: new ConsoleOutput();
    }

    /**
     * {@inheritdoc}
     */
    public function emergency($message, array $context = []): void
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function alert($message, array $context = []): void
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function critical($message, array $context = []): void
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function error($message, array $context = []): void
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function warning($message, array $context = []): void
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function notice($message, array $context = []): void
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function info($message, array $context = []): void
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function debug($message, array $context = []): void
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = []): void
    {
        $config = config('logging.' . StdoutLoggerContract::class, ['log_level' => []]);
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
        if (app()->has(WorkerState::class)) {
            $message = json_encode(['type' => 'raw', 'message' => urlencode($message)]);
        }
        $this->output->writeln($message);
    }

    protected function getMessage(string $message, string $level = LogLevel::INFO, array $tags = []): string
    {
        $tag = null;
        switch ($level) {
            case LogLevel::EMERGENCY:
            case LogLevel::ALERT:
            case LogLevel::CRITICAL:
                $tag = 'error';
                break;
            case LogLevel::ERROR:
                $tag = 'fg=red';
                break;
            case LogLevel::WARNING:
            case LogLevel::NOTICE:
                $tag = 'comment';
                break;
            case LogLevel::INFO:
            default:
                $tag = 'info';
        }

        $template = sprintf('<%s>[%s]</>', $tag, strtoupper($level));
        $implodedTags = '';
        foreach ($tags as $value) {
            $implodedTags .= (' [' . $value . ']');
        }

        return sprintf($template . $implodedTags . ' %s', $message);
    }
}
