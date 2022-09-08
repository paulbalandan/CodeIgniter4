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

use CodeIgniter\Debug\Utils;
use CodeIgniter\Exceptions\HasExitCodeInterface;
use CodeIgniter\Exceptions\HTTPExceptionInterface;
use LogicException;
use Throwable;

/**
 * `Inspector` inspects a `Throwable` and returns
 * enhanced information on it.
 *
 * @internal
 */
final class Inspector
{
    private Throwable $throwable;
    private string $file;
    private int $line;

    /**
     * The default HTTP status code in case the
     * Throwable does not provide one.
     */
    private static int $defaultStatusCode = 500;

    /**
     * The default exit code in case the Throwable
     * does not provide one.
     */
    private static int $defaultExitCode = EXIT_ERROR;

    public function __construct(Throwable $throwable)
    {
        $this->throwable = $throwable;

        [$this->file, $this->line] = Utils::extractFileLineFromEvalCode(
            $throwable->getFile(),
            $throwable->getLine()
        );
    }

    public function throwable(): Throwable
    {
        return $this->throwable;
    }

    public function name(): string
    {
        return get_class($this->throwable);
    }

    public function message(): string
    {
        return $this->throwable->getMessage();
    }

    /**
     * @throws LogicException if status code is not in the 4xx and 5xx family
     */
    public function statusCode(): int
    {
        $statusCode = self::$defaultStatusCode;

        if ($this->throwable instanceof HTTPExceptionInterface) {
            $statusCode = $this->throwable->getCode();
        }

        if ($statusCode < 400 || $statusCode >= 600) {
            throw new LogicException(sprintf(
                'HTTP status code for an exception must be in the 4xx and 5xx family, got "%d" instead.',
                $statusCode,
            ));
        }

        return $statusCode;
    }

    /**
     * @throws LogicException if exit code is not between 1 to 254
     */
    public function exitCode(): int
    {
        $exitCode = self::$defaultExitCode;

        if ($this->throwable instanceof HasExitCodeInterface) {
            $exitCode = $this->throwable->getExitCode();
        }

        if ($exitCode <= 0 || $exitCode > 255) {
            if ($exitCode === 0) {
                throw new LogicException('Exit code for an exception cannot be "0" as it signifies the operation was successful.');
            }

            throw new LogicException(sprintf(
                'Exit code for an exception must be between 1 and 255, got "%d" instead.',
                $exitCode,
            ));
        }

        return $exitCode;
    }

    public function file(): string
    {
        return $this->file;
    }

    public function line(): int
    {
        return $this->line;
    }

    public function frames(): Frames
    {
        return Frames::createFrom($this->throwable);
    }

    public function previousInspector(): ?self
    {
        $previousThrowable = $this->throwable->getPrevious();

        if ($previousThrowable === null) {
            return null;
        }

        return new self($previousThrowable);
    }
}
