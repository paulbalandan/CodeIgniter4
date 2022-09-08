<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class ErrorHandling extends BaseConfig
{
    /**
     * --------------------------------------------------------------
     * Whether to Log Errors
     * --------------------------------------------------------------
     *
     * If `true` the error handler will log the errors into the
     * log file.
     */
    public bool $logErrors = true;

    /**
     * --------------------------------------------------------------
     * HTTP Status Codes to Skip Logging
     * --------------------------------------------------------------
     *
     * All HTTP status codes here will NOT be logged if logging is
     * turned on. By default all 404 Page Not Found errors will be
     * skipped.
     *
     * @var int[]
     */
    public array $ignoreStatusCodes = [404];

    /**
     * --------------------------------------------------------------
     * PHP Error Levels to Skip Logging
     * --------------------------------------------------------------
     *
     * Any of the PHP error level constants (`E_*`) to be skipped
     * for logging when logging is turned on.
     *
     * @var int[]
     */
    public array $ignoreErrorCodes = [];

    /**
     * --------------------------------------------------------------
     * Custom Implementation of `CodeIgniter\Debug\DebugInterface`
     * --------------------------------------------------------------
     *
     * The fully qualified class name of the custom implementation
     * of the `DebugInterface`. Use `null` to use the default
     * implementation provided by the framework.
     *
     * @phpstan-var class-string<\CodeIgniter\Debug\DebugInterface>|null
     */
    public ?string $customDebugHandler = null;

    /**
     * --------------------------------------------------------------
     * Keys in Super Globals to Censor
     * --------------------------------------------------------------
     *
     * Values of keys listed here will be redacted in the super
     * globals (`$_GET`, `$_POST`, `$_ENV`, `$_SERVER`, `$_SESSION`)
     * if they are written to or displayed.
     *
     * This can accept either (1) a list of keys to be censored which
     * will be redacted in all occurences in the superglobals; or (2)
     * an associative array with the superglobal name as the key
     * (e.g., `_GET`) and a list of keys to redact from that key; or
     * (3) both list and associative array.
     *
     * ```
     * $censoredKeys = [
     *     'password', 'database.default.password',
     *     '_SERVER' => ['PHP_AUTH_PW'],
     * ];
     * ```
     *
     * @phpstan-var list<string>|array<string, list<string>>
     */
    public array $censoredKeys = [];
}
