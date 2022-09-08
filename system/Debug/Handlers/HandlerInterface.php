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

use CodeIgniter\Debug\Inspection\Inspector;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use RuntimeException;

/**
 * `HandlerInterface` defines the contract to which the registered
 * exception handlers should implement in handling the Throwable object.
 */
interface HandlerInterface
{
    /**
     * Return code to signify that the next handlers in line
     * can handle the exception.
     *
     * @final
     */
    public const STATUS_CONTINUE = 0;

    /**
     * Return code to signify that this handler is the last
     * (even though there may be few more in the queue).
     *
     * Script execution will still continue.
     *
     * @final
     */
    public const STATUS_END_OF_QUEUE = 1;

    /**
     * Return code to signify that the current handler
     * wants to terminate the script execution and
     * will `exit()`.
     *
     * @final
     */
    public const STATUS_TERMINATE = 2;

    /**
     * Handle the Throwable.
     *
     * The handler should return one of the exit codes
     * to alert the next in queue if they
     * should handle the Throwable or not.
     *
     * @phpstan-return self::STATUS_CONTINUE|self::STATUS_END_OF_QUEUE|self::STATUS_TERMINATE
     */
    public function handle(): int;

    /**
     * Checks if this handler is valid to handle the throwable.
     */
    public function valid(): bool;

    /**
     * Set an instance of Inspector.
     */
    public function setInspector(Inspector $inspector): self;

    /**
     * Retrieve the Inspector instance in this handler instance.
     *
     * @throws RuntimeException
     */
    public function getInspector(): Inspector;

    /**
     * Set an instance of the IncomingRequest class.
     */
    public function setRequest(IncomingRequest $request): self;

    /**
     * Retrieve the IncomingRequest instance in this handler instance.
     *
     * @throws RuntimeException
     */
    public function getRequest(): IncomingRequest;

    /**
     * Set an instance of the Response class.
     */
    public function setResponse(Response $response): self;

    /**
     * Retrieve the Response instance in this handler instance.
     */
    public function getResponse(): Response;
}
