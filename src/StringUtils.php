<?php

// phpcs:disable WebimpressCodingStandard.NamingConventions.AbstractClass.Prefix
declare (strict_types=1);
namespace Laminas\Stdlib;

use function array_search;
use function defined;
use function extension_loaded;
use function in_array;
use function is_string;
use Laminas\Stdlib\String_Wrapper\Iconv;
use Laminas\Stdlib\String_Wrapper\Intl;
use Laminas\Stdlib\String_Wrapper\Mb_String;
use Laminas\Stdlib\String_Wrapper\Native;
use Laminas\Stdlib\String_Wrapper\String_Wrapper_Interface;
use function preg_match;
use function strtoupper;
/**
 * Utility class for handling strings of different character encodings
 * using available PHP extensions.
 *
 * Declared abstract, as we have no need for instantiation.
 */
abstract class String_Utils
{
    /**
     * Ordered list of registered string wrapper instances
     *
     * @var list<class-string<StringWrapperInterface>>|null
     */
    protected static $wrapper_registry;
    /**
     * A list of known single-byte character encodings (upper-case)
     *
     * @var string[]
     */
    protected static $single_byte_encodings = ['ASCII', '7BIT', '8BIT', 'ISO-8859-1', 'ISO-8859-2', 'ISO-8859-3', 'ISO-8859-4', 'ISO-8859-5', 'ISO-8859-6', 'ISO-8859-7', 'ISO-8859-8', 'ISO-8859-9', 'ISO-8859-10', 'ISO-8859-11', 'ISO-8859-13', 'ISO-8859-14', 'ISO-8859-15', 'ISO-8859-16', 'CP-1251', 'CP-1252'];
    /**
     * Is PCRE compiled with Unicode support?
     *
     * @var bool
     **/
    protected static $has_pcre_unicode_support;
    /**
     * Get registered wrapper classes
     *
     * @return string[]
     * @psalm-return list<class-string<StringWrapperInterface>>
     */
    public static function get_registered_wrappers()
    {
        if (static::$wrapper_registry === null) {
            static::$wrapper_registry = [];
            if (extension_loaded('intl')) {
                static::$wrapper_registry[] = Intl::class;
            }
            if (extension_loaded('mbstring')) {
                static::$wrapper_registry[] = Mb_String::class;
            }
            if (extension_loaded('iconv')) {
                static::$wrapper_registry[] = Iconv::class;
            }
            static::$wrapper_registry[] = Native::class;
        }
        return static::$wrapper_registry;
    }
    /**
     * Register a string wrapper class
     *
     * @param class-string<StringWrapperInterface> $wrapper
     */
    public static function register_wrapper($wrapper): void
    {
        $wrapper = (string) $wrapper;
        // using getRegisteredWrappers() here to ensure that the list is initialized
        if (!in_array($wrapper, static::get_registered_wrappers(), true)) {
            static::$wrapper_registry[] = $wrapper;
        }
    }
    /**
     * Unregister a string wrapper class
     *
     * @param class-string<StringWrapperInterface> $wrapper
     */
    public static function unregister_wrapper($wrapper): void
    {
        // using getRegisteredWrappers() here to ensure that the list is initialized
        $index = array_search((string) $wrapper, static::get_registered_wrappers(), true);
        if ($index !== false) {
            unset(static::$wrapper_registry[$index]);
        }
    }
    /**
     * Reset all registered wrappers so the default wrappers will be used
     */
    public static function reset_registered_wrappers(): void
    {
        static::$wrapper_registry = null;
    }
    /**
     * Get the first string wrapper supporting the given character encoding
     * and supports to convert into the given convert encoding.
     *
     * @param string      $encoding        Character encoding to support
     * @param string|null $convertEncoding OPTIONAL character encoding to convert in
     * @return StringWrapperInterface
     * @throws Exception\RuntimeException If no wrapper supports given character encodings.
     */
    public static function get_wrapper(string $encoding = 'UTF-8', $convert_encoding = null)
    {
        foreach (static::get_registered_wrappers() as $wrapper_class) {
            if ($wrapper_class::is_supported($encoding, $convert_encoding)) {
                $wrapper = new $wrapper_class($encoding, $convert_encoding);
                $wrapper->set_encoding($encoding, $convert_encoding);
                return $wrapper;
            }
        }
        throw new Exception\RuntimeException('No wrapper found supporting "' . $encoding . '"' . ($convert_encoding !== null ? ' and "' . $convert_encoding . '"' : ''));
    }
    /**
     * Get a list of all known single-byte character encodings
     *
     * @return string[]
     */
    public static function get_single_byte_encodings()
    {
        return static::$single_byte_encodings;
    }
    /**
     * Check if a given encoding is a known single-byte character encoding
     *
     * @param string $encoding
     * @return bool
     */
    public static function is_single_byte_encoding($encoding)
    {
        return in_array(strtoupper($encoding), static::$single_byte_encodings);
    }
    /**
     * Check if a given string is valid UTF-8 encoded
     *
     * @param string $str
     * @return bool
     */
    public static function is_valid_utf8($str)
    {
        return is_string($str) && ($str === '' || preg_match('/^./su', $str) === 1);
    }
    /**
     * Is PCRE compiled with Unicode support?
     *
     * @return bool
     */
    public static function has_pcre_unicode_support()
    {
        if (static::$has_pcre_unicode_support === null) {
            Error_Handler::start();
            static::$has_pcre_unicode_support = defined('PREG_BAD_UTF8_OFFSET_ERROR') && preg_match('/\pL/u', 'a') === 1;
            Error_Handler::stop();
        }
        return static::$has_pcre_unicode_support;
    }
}