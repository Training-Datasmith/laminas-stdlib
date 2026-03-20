<?php

// phpcs:disable WebimpressCodingStandard.NamingConventions.AbstractClass.Prefix
declare (strict_types=1);
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
abstract class Error_Handler
{
    /**
     * Active stack
     *
     * @var list<ErrorException|null>
     */
    protected static $stack = [];
    /**
     * Check if this error handler is active (i.e. start() has been called more times than stop()).
     *
     * @return bool True when at least one start() call is pending a matching stop().
     */
    public static function started(): bool
    {
        return (bool) static::get_nested_level();
    }

    /**
     * Get the current nesting level (number of unmatched start() calls).
     *
     * @return int Number of active error handler frames on the stack.
     */
    public static function get_nested_level(): int
    {
        return count(static::$stack);
    }
    /**
     * Starting the error handler
     *
     * @param int $errorLevel
     */
    public static function start($error_level = E_WARNING): void
    {
        if (!static::$stack) {
            set_error_handler(static::add_error(...), $error_level);
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
    public static function stop(bool $throw = false): ?\ErrorException
    {
        $error_exception = null;
        if (static::$stack) {
            $error_exception = array_pop(static::$stack);
            if (!static::$stack) {
                restore_error_handler();
            }
            if ($error_exception && $throw) {
                throw $error_exception;
            }
        }
        return $error_exception;
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
    public static function add_error($errno, $errstr = '', $errfile = '', $errline = 0): void
    {
        $stack =& static::$stack[count(static::$stack) - 1];
        $stack = new ErrorException($errstr, 0, $errno, $errfile, $errline, $stack);
    }
}