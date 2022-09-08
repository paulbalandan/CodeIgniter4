<?php

/**
 * This file is part of CodeIgniter 4 framework.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Debug\Formatter;

interface ArgumentFormatterInterface
{
    /**
     * Formats the `$arguments` into its textual representation.
     *
     * @param bool $recursive Whether to also format inline arrays
     */
    public function format(array $arguments, bool $recursive = true): array;
}
