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

use ErrorException;
use ParseError;
use Throwable;
use TypeError;

/**
 * @internal
 */
final class Utils
{
    public static function isErrorLevelFatal(int $severity): bool
    {
        static $fatalErrors = E_ERROR | E_PARSE | E_CORE_ERROR | E_COMPILE_ERROR;

        return ($fatalErrors & $severity) !== 0;
    }

    public static function getSeverity(Throwable $throwable): int
    {
        if ($throwable instanceof ErrorException) {
            return $throwable->getSeverity();
        }

        if ($throwable instanceof ParseError) {
            return E_PARSE;
        }

        if ($throwable instanceof TypeError) {
            return E_RECOVERABLE_ERROR;
        }

        return E_ERROR;
    }

    /**
     * If the stack frame occurred in an `eval()`, the file and line
     * information is available at the left side.
     *
     * @return array<int, int|string>
     * @phpstan-return array{0: string, 1: int}
     */
    public static function extractFileLineFromEvalCode(string $file, int $line): array
    {
        if (preg_match('/^(.*)\((\d+)\) : eval\(\)\'d code$/', $file, $match) === 1 && is_file($match[1])) {
            $file = $match[1];
            $line = (int) $match[2];
        }

        return [$file, $line];
    }
}
