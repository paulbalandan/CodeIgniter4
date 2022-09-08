<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Debug;

use CodeIgniter\Debug\Handlers\HandlerInterface;
use Throwable;

/**
 * `DebugInterface` defines the contract to which the global
 * error handling manager should implement.
 */
interface DebugInterface
{
    /**
     * Register the handler as the first in line of all
     * registered handlers.
     *
     * @param HandlerInterface|string $handler
     * @phpstan-param HandlerInterface|class-string<HandlerInterface> $handler
     */
    public function prepend($handler): self;

    /**
     * Register the handler as the last in line of all
     * registered handlers.
     *
     * @param HandlerInterface|string $handler
     * @phpstan-param HandlerInterface|class-string<HandlerInterface> $handler
     */
    public function append($handler): self;

    /**
     * Remove the last registered handler and then return it.
     * Returns `null` if nothing is left in the stack.
     */
    public function pop(): ?HandlerInterface;

    /**
     * Shift the first registered handler off the stack queue.
     * Returns `null` if nothing is left in the stack.
     */
    public function shift(): ?HandlerInterface;

    /**
     * Returns the stack of registered handlers.
     *
     * @return HandlerInterface[]
     * @phpstan-return list<HandlerInterface>
     */
    public function handlers(): array;

    /**
     * Flushes the stack of registered handlers.
     */
    public function clear(): void;

    /**
     * Registers this instance to manage the error handling
     * using the following:
     *
     * - `set_exception_handler()`
     * - `set_error_handler()`
     * - `register_shutdown_function()`
     */
    public function register(): void;

    /**
     * Unregister this instance as the error handling manager,
     * using the following:
     *
     * - `restore_error_handler()`
     * - `restore_exception_handler()`
     */
    public function unregister(): void;

    /**
     * The function to register in `set_exception_handler()`.
     */
    public function handleException(Throwable $throwable): void;

    /**
     * The function to register in `set_error_handler()`.
     *
     * If the function returns `false` then the normal error handler continues.
     *
     * @param int         $errno   The level of the error
     * @param string      $errstr  The error message
     * @param string|null $errfile The filename where the error was raised
     * @param int|null    $errline The line number where the error was raised
     */
    public function handleError(int $errno, string $errstr, ?string $errfile = null, ?int $errline = null): bool;

    /**
     * The function to register in `register_shutdown_function()`.
     *
     * It is important to note that the shutdown handler should not
     * throw an exception. Otherwise, the thrown exception will not
     * be propagated to the registered exception handler.
     */
    public function handleShutdown(): void;
}
