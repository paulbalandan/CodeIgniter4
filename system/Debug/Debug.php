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
use CodeIgniter\Debug\Inspection\Inspector;
use CodeIgniter\HTTP\CLIRequest;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use Config\Services;
use ErrorException;
use InvalidArgumentException;
use Throwable;

/**
 * Default error handling manager.
 */
final class Debug implements DebugInterface, ProvidesDefaultHandlers
{
    private IncomingRequest $request;
    private Response $response;
    private int $currentOutputBufferLevel;

    /**
     * The handlers stack.
     *
     * @var array<int, HandlerInterface>
     */
    private array $stack = [];

    /**
     * Whether this instance has registered its handlers.
     */
    private bool $registered = false;

    /**
     * This flag checks if the error comes from the registered
     * shutdown handler. If it is, we forbid throwing the
     * exception as it won't be propagated to the registered
     * exception handler.
     */
    private bool $errorFromShutdownHandler = false;

    /**
     * Reserved memory so that errors can be displayed properly on memory exhaustion.
     */
    private static ?string $reservedMemory = null;

    /**
     * @param CLIRequest|IncomingRequest|null $request
     */
    public function __construct($request = null, ?Response $response = null)
    {
        $this->currentOutputBufferLevel = ob_get_level();

        $this->request  = $request ?? Services::request();
        $this->response = $response ?? Services::response();
    }

    /**
     * {@inheritDoc}
     */
    public function prepend($handler): self
    {
        array_unshift($this->stack, $this->resolve($handler));

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function append($handler): self
    {
        $this->stack[] = $this->resolve($handler);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function pop(): ?HandlerInterface
    {
        return array_pop($this->stack);
    }

    /**
     * {@inheritDoc}
     */
    public function shift(): ?HandlerInterface
    {
        return array_shift($this->stack);
    }

    /**
     * {@inheritDoc}
     */
    public function handlers(): array
    {
        return $this->stack;
    }

    /**
     * {@inheritDoc}
     */
    public function clear(): void
    {
        $this->stack = [];
    }

    /**
     * {@inheritDoc}
     */
    public function getDefaultHandlers(): array
    {
        // This list should be sorted so that non-terminating
        // handlers will go first so that we prevent unintended
        // early termination of handler queue.
        return [
            Handlers\LogErrorHandler::class,
            Handlers\ApiResponseErrorHandler::class,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function register(): void
    {
        if (self::$reservedMemory === null) {
            self::$reservedMemory = str_repeat('x', 32768);
        }

        if (! $this->registered) {
            set_exception_handler([$this, 'handleException']);
            set_error_handler([$this, 'handleError']);
            register_shutdown_function([$this, 'handleShutdown']);

            $this->registered = true;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function unregister(): void
    {
        if ($this->registered) {
            restore_exception_handler();
            restore_error_handler();

            $this->registered = false;
        }
    }

    /**
     * {@inheritDoc}
     */
    public function handleException(Throwable $throwable): void
    {
        $inspector = $this->getInspector($throwable);

        ob_start();

        $status = HandlerInterface::STATUS_CONTINUE;

        try {
            foreach ($this->stack as $handler) {
                $handler
                    ->setInspector($inspector)
                    ->setRequest($this->request)
                    ->setResponse($this->response);

                if (! $handler->valid()) {
                    continue;
                }

                $status = $handler->handle();

                if ($status !== HandlerInterface::STATUS_CONTINUE) {
                    break;
                }
            }
        } finally {
            $output = ob_get_clean();
        }

        if ($output !== false) {
            while (ob_get_level() > $this->currentOutputBufferLevel) {
                ob_end_clean();
            }

            if (! is_cli()) {
                $statusCode = $inspector->statusCode();

                try {
                    $this->response->setStatusCode($statusCode);
                } catch (HTTPException $e) {
                    $statusCode = 500;
                    $this->response->setStatusCode($statusCode);
                }

                if (! headers_sent()) {
                    header(sprintf(
                        'HTTP/%s %s %s',
                        $this->request->getProtocolVersion(),
                        $this->response->getStatusCode(),
                        $this->response->getReasonPhrase()
                    ), true, $statusCode);
                }
            }

            echo $output;
        }

        if ($status === HandlerInterface::STATUS_TERMINATE) {
            $exitCode = $inspector->exitCode();

            exit($exitCode);
        }
    }

    /**
     * {@inheritDoc}
     *
     * @throws ErrorException
     */
    public function handleError(int $errno, string $errstr, ?string $errfile = null, ?int $errline = null): bool
    {
        if ((error_reporting() & $errno) !== 0) {
            $errorAsException = new ErrorException($errstr, 0, $errno, $errfile, $errline);

            if ($this->errorFromShutdownHandler || $this->isDeprecationError($errno)) {
                $this->handleException($errorAsException);
            } else {
                throw $errorAsException;
            }

            return true;
        }

        // The severity is not included in error reporting, so
        // pass the handling to PHP's standard error handler.
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function handleShutdown(): void
    {
        if (self::$reservedMemory === null) {
            // No available memory, so this shutdown handler won't be of any help.
            return;
        }

        self::$reservedMemory = null;

        $error = error_get_last();

        if ($error === null) {
            return; // @codeCoverageIgnore
        }

        ['type' => $type, 'message' => $message, 'file' => $file, 'line' => $line] = $error;

        $this->errorFromShutdownHandler = true;

        if (Utils::isErrorLevelFatal($type)) {
            $this->handleError($type, $message, $file, $line);
        }
    }

    /**
     * Resolve the passed handler and instantiate it.
     *
     * @param HandlerInterface|string $handler
     *
     * @throws InvalidArgumentException
     */
    private function resolve($handler): HandlerInterface
    {
        if (is_string($handler)) {
            $handler = new $handler();
        }

        if ($handler instanceof HandlerInterface) {
            return $handler;
        }

        throw new InvalidArgumentException(sprintf(
            'Handler is expected to be an instance of %s but got %s instead.',
            HandlerInterface::class,
            get_class($handler)
        ));
    }

    private function getInspector(Throwable $throwable): Inspector
    {
        return new Inspector($throwable);
    }

    private function isDeprecationError(int $errno): bool
    {
        static $deprecations = E_DEPRECATED | E_USER_DEPRECATED;

        return ($deprecations & $errno) !== 0;
    }
}
