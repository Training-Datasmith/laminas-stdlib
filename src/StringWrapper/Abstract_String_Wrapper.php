<?php

declare (strict_types=1);
namespace Laminas\Stdlib\String_Wrapper;

use function floor;
use function in_array;
use Laminas\Stdlib\Exception;
use Laminas\Stdlib\String_Utils;
use function sprintf;
use function str_pad;
use const STR_PAD_BOTH;
use const STR_PAD_LEFT;
use const STR_PAD_RIGHT;
use function str_repeat;
use function strtoupper;
use function wordwrap;
abstract class Abstract_String_Wrapper implements String_Wrapper_Interface
{
    /**
     * The character encoding working on
     *
     * @var string|null
     */
    protected $encoding = 'UTF-8';
    /**
     * An optionally character encoding to convert to
     *
     * @var string|null
     */
    protected $convert_encoding;
    /**
     * Check if the given character encoding is supported by this wrapper
     * and the character encoding to convert to is also supported.
     *
     * @param  string      $encoding
     * @param  string|null $convertEncoding
     * @return bool
     */
    public static function is_supported($encoding, $convert_encoding = null)
    {
        $supported_encodings = static::get_supported_encodings();
        if (!in_array(strtoupper($encoding), $supported_encodings)) {
            return false;
        }
        if ($convert_encoding !== null && !in_array(strtoupper($convert_encoding), $supported_encodings)) {
            return false;
        }
        return true;
    }
    /**
     * Set character encoding working with and convert to
     *
     * @param string      $encoding         The character encoding to work with
     * @param string|null $convertEncoding  The character encoding to convert to
     * @return StringWrapperInterface
     */
    public function set_encoding($encoding, $convert_encoding = null)
    {
        $supported_encodings = static::get_supported_encodings();
        $encoding_upper = strtoupper($encoding);
        if (!in_array($encoding_upper, $supported_encodings)) {
            throw new Exception\InvalidArgumentException('Wrapper doesn\'t support character encoding "' . $encoding . '"');
        }
        if ($convert_encoding !== null) {
            $convert_encoding_upper = strtoupper($convert_encoding);
            if (!in_array($convert_encoding_upper, $supported_encodings)) {
                throw new Exception\InvalidArgumentException('Wrapper doesn\'t support character encoding "' . $convert_encoding . '"');
            }
            $this->convert_encoding = $convert_encoding_upper;
        } else {
            $this->convert_encoding = null;
        }
        $this->encoding = $encoding_upper;
        return $this;
    }
    /**
     * Get the defined character encoding to work with
     *
     * @return null|string
     * @throws Exception\LogicException If no encoding was defined.
     */
    public function get_encoding()
    {
        return $this->encoding;
    }
    /**
     * Get the defined character encoding to convert to
     *
     * @return string|null
     */
    public function get_convert_encoding()
    {
        return $this->convert_encoding;
    }
    /**
     * Convert a string from defined character encoding to the defined convert encoding
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
        $from = $reverse ? $convert_encoding : $encoding;
        $to = $reverse ? $encoding : $convert_encoding;
        throw new Exception\RuntimeException(sprintf('Converting from "%s" to "%s" isn\'t supported by this string wrapper', $from ?? '', $to ?? ''));
    }
    /**
     * Wraps a string to a given number of characters
     *
     * @param  string  $string
     * @param  int $width
     * @param  string  $break
     * @param  bool $cut
     * @return string|false
     */
    public function word_wrap($string, $width = 75, $break = "\n", $cut = false)
    {
        $string = (string) $string;
        if ($string === '') {
            return '';
        }
        $break = (string) $break;
        if ($break === '') {
            throw new Exception\InvalidArgumentException('Break string cannot be empty');
        }
        $width = (int) $width;
        if ($width === 0 && $cut) {
            throw new Exception\InvalidArgumentException('Cannot force cut when width is zero');
        }
        if (null === $this->get_encoding() || String_Utils::is_single_byte_encoding($this->get_encoding())) {
            return wordwrap($string, $width, $break, $cut);
        }
        $string_width = $this->strlen($string);
        $break_width = $this->strlen($break);
        $result = '';
        $last_start = $last_space = 0;
        for ($current = 0; $current < $string_width; $current++) {
            $char = $this->substr($string, $current, 1);
            $possible_break = $char;
            if ($break_width !== 1) {
                $possible_break = $this->substr($string, $current, $break_width);
            }
            if ($possible_break === $break) {
                $result .= $this->substr($string, $last_start, $current - $last_start + $break_width);
                $current += $break_width - 1;
                $last_start = $last_space = $current + 1;
                continue;
            }
            if ($char === ' ') {
                if ($current - $last_start >= $width) {
                    $result .= $this->substr($string, $last_start, $current - $last_start) . $break;
                    $last_start = $current + 1;
                }
                $last_space = $current;
                continue;
            }
            if ($current - $last_start >= $width && $cut && $last_start >= $last_space) {
                $result .= $this->substr($string, $last_start, $current - $last_start) . $break;
                $last_start = $last_space = $current;
                continue;
            }
            if ($current - $last_start >= $width && $last_start < $last_space) {
                $result .= $this->substr($string, $last_start, $last_space - $last_start) . $break;
                $last_start = $last_space += 1;
                continue;
            }
        }
        if ($last_start !== $current) {
            $result .= $this->substr($string, $last_start, $current - $last_start);
        }
        return $result;
    }
    /**
     * Pad a string to a certain length with another string
     *
     * @param  string  $input
     * @param  int $padLength
     * @param  string  $padString
     * @param  int $padType
     * @return string
     */
    public function str_pad($input, $pad_length, $pad_string = ' ', $pad_type = STR_PAD_RIGHT)
    {
        if (null === $this->get_encoding() || String_Utils::is_single_byte_encoding($this->get_encoding())) {
            return str_pad($input, $pad_length, $pad_string, $pad_type);
        }
        $length_of_padding = $pad_length - $this->strlen($input);
        if ($length_of_padding <= 0) {
            return $input;
        }
        $pad_string_length = $this->strlen($pad_string);
        if ($pad_string_length === 0) {
            return $input;
        }
        $repeat_count = (int) floor($length_of_padding / $pad_string_length);
        if ($pad_type === STR_PAD_BOTH) {
            $repeat_count_left = $repeat_count_right = ($repeat_count - $repeat_count % 2) / 2;
            $last_string_length = $length_of_padding - 2 * $repeat_count_left * $pad_string_length;
            $last_string_left_length = $last_string_right_length = (int) floor($last_string_length / 2);
            $last_string_right_length += $last_string_length % 2;
            $last_string_left = $this->substr($pad_string, 0, $last_string_left_length);
            $last_string_right = $this->substr($pad_string, 0, $last_string_right_length);
            return str_repeat($pad_string, $repeat_count_left) . $last_string_left . $input . str_repeat($pad_string, $repeat_count_right) . $last_string_right;
        }
        $last_string = $this->substr($pad_string, 0, $length_of_padding % $pad_string_length);
        if ($pad_type === STR_PAD_LEFT) {
            return str_repeat($pad_string, $repeat_count) . $last_string . $input;
        }
        return $input . str_repeat($pad_string, $repeat_count) . $last_string;
    }
}