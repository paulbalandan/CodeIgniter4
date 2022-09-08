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

/**
 * A default implementation of an argument formatter.
 *
 * @internal
 */
final class ArgumentFormatter implements ArgumentFormatterInterface
{
    private const MAX_ARRAY_ITEMS = 5;

    /**
     * {@inheritDoc}
     */
    public function format(array $arguments, bool $recursive = true, bool $cleanPath = true): array
    {
        $formatted = [];

        foreach ($arguments as $argument) {
            switch (true) {
                case is_object($argument):
                    $formatted[] = sprintf('Object(%s)', get_class($argument));
                    break;

                case is_array($argument):
                    if ($recursive && count($argument) <= self::MAX_ARRAY_ITEMS) {
                        $formatted[] = sprintf('[%s]', implode(', ', $this->format($argument, false)));
                    }

                    break;

                case $argument === null:
                    $formatted[] = 'null';
                    break;

                case is_resource($argument):
                    $formatted[] = sprintf('resource (%s)', get_resource_type($argument));
                    break;

                default:
                    $formatted[] = var_export($argument, true);
            }
        }

        return $formatted;
    }
}
