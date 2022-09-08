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

interface ProvidesDefaultHandlers
{
    /**
     * Retrieves the default error handlers for
     * consumption by `DebugInterface` instance.
     *
     * @return string[]
     * @phpstan-return array<class-string<\CodeIgniter\Debug\Handlers\HandlerInterface>>
     */
    public function getDefaultHandlers(): array;
}
