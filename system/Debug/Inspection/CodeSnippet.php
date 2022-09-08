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

/**
 * @internal
 */
final class CodeSnippet
{
    private string $file;
    private int $line;
    private int $lines;

    public function __construct(string $file, int $line, int $lines = 15)
    {
        $this->file = $file;
        $this->line = $line;

        $this->lines = $lines;
    }

    /**
     * @return array<int, string>
     */
    public function get(): array
    {
        if (! is_file($this->file)) {
            return [];
        }

        $lines = file($this->file);

        if ($lines === false) {
            return [];
        }

        [$start, $end] = $this->getBounds(count($lines));

        $slices = array_slice($lines, $start, $end - $start + 1, true);

        $code = [];

        foreach ($slices as $index => $slice) {
            $code[$index + 1] = rtrim($slice);
        }

        return $code;
    }

    /**
     * @return int[]
     * @phpstan-return array{0: int, 1: int}
     */
    private function getBounds(int $totalLines): array
    {
        $start = (int) max($this->line - floor($this->lines / 2), 0);

        $end = $start + $this->lines - 1;

        if ($end > $totalLines) {
            $end   = $totalLines;
            $start = (int) max($end - $this->lines + 1, 0);
        }

        return [$start, $end];
    }
}
