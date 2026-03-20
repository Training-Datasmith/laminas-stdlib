<?php

declare (strict_types=1);
namespace Laminas\Stdlib\String_Wrapper;

use function in_array;
use Laminas\Stdlib\Exception;
use Laminas\Stdlib\String_Utils;
use function strlen;
use function strpos;
use function strtoupper;
use function substr;
/** @final */
class Native extends Abstract_String_Wrapper
{
    /**
     * The character encoding working on
     * (overwritten to change default encoding)
     *
     * @var string
     */
    protected $encoding = 'ASCII';
    /**
     * Check if the given character encoding is supported by this wrapper
     * and the character encoding to convert to is also supported.
     *
     * @param  string      $encoding
     * @param  string|null $convertEncoding
     */
    public static function is_supported($encoding, $convert_encoding = null): bool
    {
        $encoding_upper = strtoupper($encoding);
        $supported_encodings = static::get_supported_encodings();
        if (!in_array($encoding_upper, $supported_encodings)) {
            return false;
        }
        // This adapter doesn't support to convert between encodings
        if ($convert_encoding !== null && $encoding_upper !== strtoupper($convert_encoding)) {
            return false;
        }
        return true;
    }
    /**
     * Get a list of supported character encodings
     *
     * @return string[]
     */
    public static function get_supported_encodings()
    {
        return String_Utils::get_single_byte_encodings();
    }
    /**
     * Set character encoding working with and convert to
     *
     * @param string      $encoding         The character encoding to work with
     * @param string|null $convertEncoding  The character encoding to convert to
     * @return StringWrapperInterface
     */
    public function set_encoding($encoding, $convert_encoding = null): static
    {
        $supported_encodings = static::get_supported_encodings();
        $encoding_upper = strtoupper($encoding);
        if (!in_array($encoding_upper, $supported_encodings)) {
            throw new Exception\InvalidArgumentException('Wrapper doesn\'t support character encoding "' . $encoding . '"');
        }
        if (null !== $convert_encoding && $encoding_upper !== strtoupper($convert_encoding)) {
            $this->convert_encoding = $encoding_upper;
        }
        if ($convert_encoding !== null) {
            if ($encoding_upper !== strtoupper($convert_encoding)) {
                throw new Exception\InvalidArgumentException('Wrapper doesn\'t support to convert between character encodings');
            }
            $this->convert_encoding = $encoding_upper;
        } else {
            $this->convert_encoding = null;
        }
        $this->encoding = $encoding_upper;
        return $this;
    }
    /**
     * Returns the length of the given string
     *
     * @param string $str
     * @return int|false
     */
    public function strlen($str): int
    {
        return strlen($str);
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
        return substr($str, $offset, $length);
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
        return strpos($haystack, $needle, $offset);
    }
}