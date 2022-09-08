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

use CodeIgniter\Debug\Formatter\ArgumentFormatter;
use CodeIgniter\Debug\Formatter\ArgumentFormatterInterface;
use CodeIgniter\Debug\Inspection\Inspector;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use Config\ErrorHandling;
use RuntimeException;

abstract class AbstractErrorHandler implements HandlerInterface
{
    protected ErrorHandling $config;
    protected ArgumentFormatterInterface $argumentFormatter;
    protected ?Inspector $inspector     = null;
    protected ?IncomingRequest $request = null;
    protected ?Response $response       = null;

    public function __construct(
        ?ErrorHandling $config = null,
        ?ArgumentFormatterInterface $argumentFormatter = null
    ) {
        $this->config = $config ?? config(ErrorHandling::class);

        $this->argumentFormatter = $argumentFormatter ?? new ArgumentFormatter();
    }

    /**
     * {@inheritDoc}
     */
    public function setInspector(Inspector $inspector): self
    {
        $this->inspector = $inspector;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getInspector(): Inspector
    {
        if ($this->inspector === null) {
            throw new RuntimeException('Instance of Inspector is not available yet.');
        }

        return $this->inspector;
    }

    /**
     * {@inheritDoc}
     */
    public function setRequest(IncomingRequest $request): self
    {
        $this->request = $request;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getRequest(): IncomingRequest
    {
        if ($this->request === null) {
            throw new RuntimeException('Instance of IncomingRequest is not available yet.');
        }

        return $this->request;
    }

    /**
     * {@inheritDoc}
     */
    public function setResponse(Response $response): self
    {
        $this->response = $response;

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getResponse(): Response
    {
        if ($this->response === null) {
            throw new RuntimeException('Instance of Response is not available yet.');
        }

        return $this->response;
    }
}
