<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Debug\Inspection;

use ArrayIterator;
use CodeIgniter\Debug\Utils;
use ErrorException;
use IteratorAggregate;
use Throwable;
use Traversable;

/**
 * `Frames` improves the stack trace information by ensuring all
 * elements of frame belongs to the current frame.
 *
 * @internal
 *
 * @implements IteratorAggregate<int, Frame>
 */
final class Frames implements IteratorAggregate
{
    private array $frames = [];

    private function __construct(Throwable $throwable)
    {
        $this->generateFrames($throwable);
    }

    public static function createFrom(Throwable $throwable): self
    {
        return new self($throwable);
    }

    public function frames(): array
    {
        return $this->frames;
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->frames());
    }

    public function toArray(): array
    {
        return array_map(
            static fn (Frame $frame): array => $frame->toArray(),
            $this->frames
        );
    }

    /**
     * Returns a list of `Frame` objects for each stack frame.
     * The frames returned include the frame for the main
     * exception prepended to the stack trace.
     */
    private function generateFrames(Throwable $throwable): void
    {
        $frames = [];

        [$currentFile, $currentLine] = Utils::extractFileLineFromEvalCode(
            $throwable->getFile(),
            $throwable->getLine()
        );

        foreach ($this->getTrace($throwable) as $trace) {
            $frames[] = new Frame(
                $currentFile,
                $currentLine,
                $trace['function'],
                $trace['class'] ?? '',
                $trace['type'] ?? '',
                $trace['args'] ?? []
            );

            [$currentFile, $currentLine] = Utils::extractFileLineFromEvalCode(
                $trace['file'] ?? '[internal function]',
                $trace['line'] ?? 0
            );
        }

        $frames[] = new Frame($currentFile, $currentLine, '[main]');

        $previous = $throwable->getPrevious();

        if ($previous !== null) {
            $previousFrames = (new self($previous))->frames();
        }

        $this->frames = $frames;
    }

    /**
     * Get the stack trace from the Throwable. If the throwable
     * is an instance of `ErrorException` and `xdebug` is enabled,
     * retrieve the more detailed stack trace from `xdebug_get_function_stack()`.
     */
    private function getTrace(Throwable $throwable): array
    {
        $traces = $throwable->getTrace();

        if (! $throwable instanceof ErrorException) {
            return $traces;
        }

        if (! Utils::isErrorLevelFatal($throwable->getSeverity())) {
            return $traces;
        }

        if (! $this->isXdebugEnabled()) {
            return $traces;
        }

        $xdebugTrace = array_reverse(xdebug_get_function_stack());
        $debugTrace  = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);

        return array_diff_key($xdebugTrace, $debugTrace);
    }

    private function isXdebugEnabled(): bool
    {
        return extension_loaded('xdebug')
            && function_exists('xdebug_is_enabled')
            && xdebug_is_enabled();
    }
}
