<?php

// phpcs:disable WebimpressCodingStandard.NamingConventions.AbstractClass.Prefix

declare(strict_types=1);

namespace Laminas\Stdlib;

use function array_pop;

use function count;

use const E_WARNING;

use ErrorException;

use function restore_error_handler;

use function set_error_handler;

/**
 * ErrorHandler that can be used to catch internal PHP errors
 * and convert to an ErrorException instance.
 */
abstract class ErrorHandler
{
    /**
     * Active stack
     *
     * @var list<ErrorException|null>
     */
    protected static $stack = [];

    /**
     * Check if this error handler is active
     *
     * @return bool
     */
    public static function started()
    {
        return (bool) static::getNestedLevel();
    }

    /**
     * Get the current nested level
     *
     * @return int
     */
    public static function getNestedLevel()
    {
        return count(static::$stack);
    }

    /**
     * Starting the error handler
     *
     * @param int $errorLevel
     */
    public static function start($errorLevel = E_WARNING): void
    {
        if (! static::$stack) {
            set_error_handler(static::addError(...), $errorLevel);
        }

        static::$stack[] = null;
    }

    /**
     * Stopping the error handler
     *
     * @param  bool $throw Throw the ErrorException if any
     * @return null|ErrorException
     * @throws ErrorException If an error has been caught and $throw is true.
     */
    public static function stop($throw = false)
    {
        $errorException = null;

        if (static::$stack) {
            $errorException = array_pop(static::$stack);

            if (! static::$stack) {
                restore_error_handler();
            }

            if ($errorException && $throw) {
                throw $errorException;
            }
        }

        return $errorException;
    }

    /**
     * Stop all active handler
     */
    public static function clean(): void
    {
        if (static::$stack) {
            restore_error_handler();
        }

        static::$stack = [];
    }

    /**
     * Add an error to the stack
     *
     * @param int    $errno
     * @param string $errstr
     * @param string $errfile
     * @param int    $errline
     */
    public static function addError($errno, $errstr = '', $errfile = '', $errline = 0): void
    {
        $stack = &static::$stack[count(static::$stack) - 1];
        $stack = new ErrorException($errstr, 0, $errno, $errfile, $errline, $stack);
    }
}
