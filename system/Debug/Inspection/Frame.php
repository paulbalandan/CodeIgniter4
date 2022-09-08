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
 * A `Frame` represents a single stack frame from a trace.
 *
 * @internal
 */
final class Frame
{
    /**
     * The current file name, or `[internal function]`.
     */
    private string $file;

    /**
     * The current line number.
     */
    private int $line;

    /**
     * The current function name.
     */
    private string $function;

    /**
     * The current class name, or `''` if none.
     */
    private string $class = '';

    /**
     * The current call type, if class is available.
     */
    private string $type = '';

    /**
     * The list of function arguments.
     */
    private array $arguments = [];

    public function __construct(
        string $file,
        int $line,
        string $function,
        string $class = '',
        string $type = '',
        array $arguments = []
    ) {
        $this->file = $file;
        $this->line = $line;

        $this->class = $class;
        $this->type  = $type;

        $this->function = $function;

        $this->arguments = $arguments;
    }

    public function getFile(): string
    {
        return $this->file;
    }

    public function getLine(): int
    {
        return $this->line;
    }

    public function getFunction(): string
    {
        return $this->function;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function getCodeSnippet(int $lines = 15): array
    {
        return (new CodeSnippet($this->file, $this->line, $lines))->get();
    }

    public function toArray(): array
    {
        return [
            'file'         => $this->file,
            'line'         => $this->line,
            'class'        => $this->class,
            'type'         => $this->type,
            'function'     => $this->function,
            'arguments'    => $this->arguments,
            'code_snippet' => $this->getCodeSnippet(),
        ];
    }
}
