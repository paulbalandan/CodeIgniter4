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

use CodeIgniter\API\ResponseTrait;

/**
 * An error handler that responds to exceptions thrown in API calls.
 */
final class ApiResponseErrorHandler extends AbstractErrorHandler
{
    use ResponseTrait;

    /**
     * {@inheritDoc}
     */
    public function valid(): bool
    {
        return strpos($this->request->getHeaderLine('accept'), 'text/html') === false;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(): int
    {
        $data = '';

        if (ENVIRONMENT !== 'production') {
            $data = $this->getResponseData();
        }

        $this->respond($data, $this->getInspector()->statusCode())->send();

        return self::STATUS_TERMINATE;
    }

    private function getResponseData(): array
    {
        $inspector = $this->getInspector();

        $frames = array_map(function (array $frame): array {
            unset($frame['code_snippet']);
            $frame['arguments'] = $this->argumentFormatter->format($frame['arguments']);

            return $frame;
        }, $inspector->frames()->toArray());

        return [
            'title'   => $inspector->name(),
            'code'    => $inspector->statusCode(),
            'message' => $inspector->message(),
            'file'    => $inspector->file(),
            'line'    => $inspector->line(),
            'frames'  => $frames,
        ];
    }
}
