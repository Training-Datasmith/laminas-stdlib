<?php

declare (strict_types=1);
namespace Laminas\Stdlib\String_Wrapper;

use function array_map;
use function array_search;
use function extension_loaded;
use Laminas\Stdlib\Exception;
use function mb_convert_encoding;
use function mb_list_encodings;
use function mb_strlen;
use function mb_strpos;
use function mb_substr;
/** @final */
class Mb_String extends Abstract_String_Wrapper
{
    /**
     * List of supported character sets (upper case)
     *
     * @link http://php.net/manual/mbstring.supported-encodings.php
     *
     * @var null|string[]
     */
    protected static $encodings;
    /**
     * Get a list of supported character encodings
     *
     * @return string[]
     */
    public static function get_supported_encodings()
    {
        if (static::$encodings === null) {
            static::$encodings = array_map(strtoupper(...), mb_list_encodings());
            // FIXME: Converting € (UTF-8) to ISO-8859-16 gives a wrong result
            $index_iso885916 = array_search('ISO-8859-16', static::$encodings, true);
            if ($index_iso885916 !== false) {
                unset(static::$encodings[$index_iso885916]);
            }
        }
        return static::$encodings;
    }
    /**
     * Constructor
     *
     * @throws Exception\ExtensionNotLoadedException
     */
    public function __construct()
    {
        if (!extension_loaded('mbstring')) {
            throw new Exception\Extension_Not_Loaded_Exception('PHP extension "mbstring" is required for this wrapper');
        }
    }
    /**
     * Returns the length of the given string
     *
     * @param string $str
     * @return int|false
     */
    public function strlen($str): int
    {
        return mb_strlen($str, $this->get_encoding());
    }
    /**
     * Returns the portion of string specified by the start and length parameters
     *
     * @param string   $str
     * @param int      $offset
     * @param int|null $length
     * @return string|false
     */
    public function substr($str, $offset = 0, $length = null): string
    {
        return mb_substr($str, $offset, $length, $this->get_encoding());
    }
    /**
     * Find the position of the first occurrence of a substring in a string
     *
     * @param string $haystack
     * @param string $needle
     * @param int    $offset
     * @return int|false
     */
    public function strpos($haystack, $needle, $offset = 0): int|false
    {
        return mb_strpos($haystack, $needle, $offset, $this->get_encoding());
    }
    /**
     * Convert a string from defined encoding to the defined convert encoding
     *
     * @param string  $str
     * @param bool $reverse
     * @return string|false
     */
    public function convert($str, $reverse = false)
    {
        $encoding = $this->get_encoding();
        $convert_encoding = $this->get_convert_encoding();
        if ($convert_encoding === null) {
            throw new Exception\LogicException('No convert encoding defined');
        }
        if ($encoding === $convert_encoding) {
            return $str;
        }
        $from_encoding = $reverse ? $convert_encoding : $encoding;
        $to_encoding = $reverse ? $encoding : $convert_encoding;
        return mb_convert_encoding($str, $to_encoding ?? '', $from_encoding ?? '');
    }
}