<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Debug\Handlers;

use CodeIgniter\Debug\Formatter\ArgumentFormatterInterface;
use CodeIgniter\Debug\Inspection\Frame;
use CodeIgniter\Debug\Inspection\Inspector;
use CodeIgniter\Debug\Utils;
use Config\ErrorHandling;
use Config\Services;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;

final class LogErrorHandler extends AbstractErrorHandler
{
    private LoggerInterface $logger;

    /**
     * A mapping of PHP error levels to their corresponding log level.
     *
     * @var array<int, string>
     */
    private array $errorLevelMap = [
        E_DEPRECATED        => LogLevel::WARNING,
        E_USER_DEPRECATED   => LogLevel::WARNING,
        E_NOTICE            => LogLevel::WARNING,
        E_USER_NOTICE       => LogLevel::WARNING,
        E_STRICT            => LogLevel::WARNING,
        E_COMPILE_WARNING   => LogLevel::WARNING,
        E_CORE_WARNING      => LogLevel::WARNING,
        E_USER_WARNING      => LogLevel::WARNING,
        E_WARNING           => LogLevel::WARNING,
        E_COMPILE_ERROR     => LogLevel::CRITICAL,
        E_CORE_ERROR        => LogLevel::CRITICAL,
        E_ERROR             => LogLevel::CRITICAL,
        E_RECOVERABLE_ERROR => LogLevel::CRITICAL,
        E_USER_ERROR        => LogLevel::CRITICAL,
        E_PARSE             => LogLevel::CRITICAL,
    ];

    public function __construct(
        ?ErrorHandling $config = null,
        ?ArgumentFormatterInterface $formatter = null,
        ?LoggerInterface $logger = null
    ) {
        parent::__construct($config, $formatter);

        $this->logger = $logger ?? Services::logger();
    }

    /**
     * {@inheritDoc}
     */
    public function valid(): bool
    {
        $status = $this->getInspector()->statusCode();
        $error  = Utils::getSeverity($this->getInspector()->throwable());

        return $this->config->logErrors === true
            && ! in_array($status, $this->config->ignoreStatusCodes, true)
            && ! in_array($error, $this->config->ignoreErrorCodes, true);
    }

    /**
     * {@inheritDoc}
     */
    public function handle(): int
    {
        $error = Utils::getSeverity($this->getInspector()->throwable());
        $level = $this->errorLevelMap[$error];

        $this->logger->log($level, $this->generateMessage());

        return self::STATUS_CONTINUE;
    }

    private function generateMessage(): string
    {
        $inspector = $this->getInspector();
        $message   = $this->getMessageLine($inspector);

        while (null !== $inspector = $inspector->previousInspector()) {
            $message .= 'Caused by ' . $this->getMessageLine($inspector);
        }

        $message .= $this->getFramesAsString();

        return $message;
    }

    private function getMessageLine(Inspector $inspector): string
    {
        return sprintf(
            "[%s] %s in file %s on line %d.\n",
            $inspector->name(),
            $inspector->message(),
            clean_path($inspector->file()),
            $inspector->line()
        );
    }

    private function getFramesAsString(): string
    {
        $output = "Stack trace:\n";

        /** @var Frame $frame */
        foreach ($this->getInspector()->frames() as $index => $frame) {
            $function  = $frame->getFunction();
            $arguments = implode(', ', $this->argumentFormatter->format($frame->getArguments()));

            $output .= sprintf(
                "%2d %s%s: %s%s%s%s\n",
                ++$index,
                clean_path($frame->getFile()),
                $frame->getLine() !== 0 ? '(' . $frame->getLine() . ')' : '',
                $frame->getClass(),
                $frame->getType(),
                $function,
                $function === '[main]' ? '' : '(' . $arguments . ')'
            );
        }

        return $output;
    }
}
