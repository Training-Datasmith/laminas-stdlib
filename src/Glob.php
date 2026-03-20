<?php

// phpcs:disable WebimpressCodingStandard.NamingConventions.AbstractClass.Prefix,Generic.NamingConventions.ConstructorName.OldStyle
declare (strict_types=1);
namespace Laminas\Stdlib;

use function array_merge;
use function array_unique;
use function defined;
use function glob;
use const GLOB_BRACE;
use const GLOB_ERR;
use const GLOB_MARK;
use const GLOB_NOCHECK;
use const GLOB_NOESCAPE;
use const GLOB_NOSORT;
use const GLOB_ONLYDIR;
use function strlen;
use function strpos;
use function substr;
/**
 * Wrapper for glob with fallback if GLOB_BRACE is not available.
 */
abstract class Glob
{
    /**#@+
     * Glob constants.
     */
    public const GLOB_MARK = 0x1;
    public const GLOB_NOSORT = 0x2;
    public const GLOB_NOCHECK = 0x4;
    public const GLOB_NOESCAPE = 0x8;
    public const GLOB_BRACE = 0x10;
    public const GLOB_ONLYDIR = 0x20;
    public const GLOB_ERR = 0x40;
    /**#@-*/
    /**
     * Find pathnames matching a pattern.
     *
     * @see    http://docs.php.net/glob
     *
     * @param  string  $pattern
     * @param  int $flags
     * @param  bool $forceFallback
     * @return array
     * @throws Exception\RuntimeException
     */
    public static function glob($pattern, $flags = 0, $force_fallback = false)
    {
        if (!defined('GLOB_BRACE') || $force_fallback) {
            return static::fallback_glob($pattern, $flags);
        }
        return static::system_glob($pattern, $flags);
    }
    /**
     * Use the glob function provided by the system.
     *
     * @param  string  $pattern
     * @param  int     $flags
     * @return array
     * @throws Exception\RuntimeException
     */
    protected static function system_glob($pattern, $flags)
    {
        if ($flags) {
            $flag_map = [self::GLOB_MARK => GLOB_MARK, self::GLOB_NOSORT => GLOB_NOSORT, self::GLOB_NOCHECK => GLOB_NOCHECK, self::GLOB_NOESCAPE => GLOB_NOESCAPE, self::GLOB_BRACE => defined('GLOB_BRACE') ? GLOB_BRACE : 0, self::GLOB_ONLYDIR => GLOB_ONLYDIR, self::GLOB_ERR => GLOB_ERR];
            $glob_flags = 0;
            foreach ($flag_map as $internal_flag => $glob_flag) {
                if ($flags & $internal_flag) {
                    $glob_flags |= $glob_flag;
                }
            }
        } else {
            $glob_flags = 0;
        }
        Error_Handler::start();
        $res = glob($pattern, $glob_flags);
        $err = Error_Handler::stop();
        if ($res === false) {
            throw new Exception\RuntimeException("glob('{$pattern}', {$glob_flags}) failed", 0, $err);
        }
        return $res;
    }
    /**
     * Expand braces manually, then use the system glob.
     *
     * @param  string  $pattern
     * @param  int     $flags
     * @return array
     * @throws Exception\RuntimeException
     */
    protected static function fallback_glob($pattern, $flags)
    {
        if (!self::flags_is_equal_to($flags, self::GLOB_BRACE)) {
            return static::system_glob($pattern, $flags);
        }
        $flags &= ~self::GLOB_BRACE;
        $length = strlen($pattern);
        $paths = [];
        if ($flags & self::GLOB_NOESCAPE) {
            $begin = strpos($pattern, '{');
        } else {
            $begin = 0;
            while (true) {
                if ($begin === $length) {
                    $begin = false;
                    break;
                } elseif ($pattern[$begin] === '\\' && $begin + 1 < $length) {
                    $begin++;
                } elseif ($pattern[$begin] === '{') {
                    break;
                }
                $begin++;
            }
        }
        if ($begin === false) {
            return static::system_glob($pattern, $flags);
        }
        $next = static::next_brace_sub($pattern, $begin + 1, $flags);
        if ($next === null) {
            return static::system_glob($pattern, $flags);
        }
        $rest = $next;
        while ($pattern[$rest] !== '}') {
            $rest = static::next_brace_sub($pattern, $rest + 1, $flags);
            if ($rest === null) {
                return static::system_glob($pattern, $flags);
            }
        }
        $p = $begin + 1;
        while (true) {
            $sub_pattern = substr($pattern, 0, $begin) . substr($pattern, $p, $next - $p) . substr($pattern, $rest + 1);
            $result = static::fallback_glob($sub_pattern, $flags | self::GLOB_BRACE);
            if ($result) {
                $paths = array_merge($paths, $result);
            }
            if ($pattern[$next] === '}') {
                break;
            }
            $p = $next + 1;
            $next = static::next_brace_sub($pattern, $p, $flags);
        }
        return array_unique($paths);
    }
    /**
     * Find the end of the sub-pattern in a brace expression.
     *
     * @param  string  $pattern
     * @param  int $begin
     * @return int|null
     */
    protected static function next_brace_sub($pattern, $begin, int $flags)
    {
        $length = strlen($pattern);
        $depth = 0;
        $current = $begin;
        while ($current < $length) {
            $flags_equals_no_escape = self::flags_is_equal_to($flags, self::GLOB_NOESCAPE);
            if ($flags_equals_no_escape && $pattern[$current] === '\\') {
                if (++$current === $length) {
                    break;
                }
                $current++;
            } else if ($pattern[$current] === '}' && $depth-- === 0 || $pattern[$current] === ',' && $depth === 0) {
                break;
            } elseif ($pattern[$current++] === '{') {
                $depth++;
            }
        }
        return $current < $length ? $current : null;
    }
    /** @internal */
    public static function flags_is_equal_to(int $flags, int $other_flags): bool
    {
        return (bool) ($flags & $other_flags);
    }
}