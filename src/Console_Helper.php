<?php

declare (strict_types=1);
namespace Laminas\Stdlib;

use const DIRECTORY_SEPARATOR;
use function function_exists;
use function fwrite;
use function getenv;
use const PHP_EOL;
use function posix_isatty;
use function preg_replace;
use function sprintf;
use const STDERR;
use const STDOUT;
use function str_replace;
/**
 * Utilities for console tooling.
 *
 * Provides the following facilities:
 *
 * - Colorize strings using markup (e.g., `<info>message</info>`,
 *   `<error>message</error>`)
 * - Write output to a specified stream, optionally with colorization.
 * - Write a line of output to a specified stream, optionally with
 *   colorization, using the system EOL sequence..
 * - Write an error message to STDERR.
 *
 * Colorization will only occur when expected sequences are discovered, and
 * then, only if the console terminal allows it.
 *
 * Essentially, provides the bare minimum to allow you to provide messages to
 * the current console.
 */
class Console_Helper
{
    public const COLOR_GREEN = "\x1b[32m";
    public const COLOR_RED = "\x1b[31m";
    public const COLOR_RESET = "\x1b[0m";
    public const HIGHLIGHT_INFO = 'info';
    public const HIGHLIGHT_ERROR = 'error';
    /** @psalm-var array<ConsoleHelper::HIGHLIGHT_*, ConsoleHelper::COLOR_GREEN|ConsoleHelper::COLOR_RED> */
    private array $highlight_map = [self::HIGHLIGHT_INFO => self::COLOR_GREEN, self::HIGHLIGHT_ERROR => self::COLOR_RED];
    /** @var string Exists only for testing. */
    private string $eol = PHP_EOL;
    /** @var resource Exists only for testing. */
    private $stderr = STDERR;
    private readonly bool $supports_color;
    /**
     * @param resource $resource
     */
    public function __construct($resource = STDOUT)
    {
        $this->supports_color = $this->detect_color_capabilities($resource);
    }
    /**
     * Colorize a string for use with the terminal.
     *
     * Takes strings formatted as `<key>string</key>` and formats them per the
     * $highlightMap; if color support is disabled, simply removes the formatting
     * tags.
     *
     * @param string $string
     * @return string
     */
    public function colorize($string)
    {
        $reset = $this->supports_color ? self::COLOR_RESET : '';
        foreach ($this->highlight_map as $key => $color) {
            $pattern = sprintf('#<%s>(.*?)</%s>#s', $key, $key);
            $color = $this->supports_color ? $color : '';
            $string = (string) preg_replace($pattern, $color . '$1' . $reset, $string);
        }
        return $string;
    }
    /**
     * @param string $string
     * @param bool $colorize Whether or not to colorize the string
     * @param resource $resource Defaults to STDOUT
     */
    public function write($string, $colorize = true, $resource = STDOUT): void
    {
        if ($colorize) {
            $string = $this->colorize($string);
        }
        $string = $this->format_newlines($string);
        fwrite($resource, $string);
    }
    /**
     * @param bool $colorize Whether or not to colorize the line
     * @param resource $resource Defaults to STDOUT
     */
    public function write_line(string $string, $colorize = true, $resource = STDOUT): void
    {
        $this->write($string . $this->eol, $colorize, $resource);
    }
    /**
     * Emit an error message.
     *
     * Wraps the message in `<error></error>`, and passes it to `writeLine()`,
     * using STDERR as the resource; emits an additional empty line when done,
     * also to STDERR.
     */
    public function write_error_message(string $message): void
    {
        $this->write_line(sprintf('<error>%s</error>', $message), true, $this->stderr);
        $this->write_line('', false, $this->stderr);
    }
    /**
     * @param resource $resource
     */
    private function detect_color_capabilities($resource = STDOUT): bool
    {
        if ('\\' === DIRECTORY_SEPARATOR) {
            // Windows
            return false !== getenv('ANSICON') || 'ON' === getenv('ConEmuANSI') || 'xterm' === getenv('TERM');
        }
        return function_exists('posix_isatty') && posix_isatty($resource);
    }
    /**
     * Ensure newlines are appropriate for the current terminal.
     *
     * @param string $string
     */
    private function format_newlines($string): string
    {
        $string = str_replace($this->eol, "\x00PHP_EOL\x00", $string);
        $string = (string) preg_replace("/(\r\n|\n|\r)/", $this->eol, $string);
        return str_replace("\x00PHP_EOL\x00", $this->eol, $string);
    }
}