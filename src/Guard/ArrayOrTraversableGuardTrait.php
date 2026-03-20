<?php

declare (strict_types=1);
namespace Laminas\Stdlib\Guard;

use Exception;
use function get_debug_type;
use function is_array;
use Laminas\Stdlib\Exception\InvalidArgumentException;
use function sprintf;
use Traversable;
/**
 * Provide a guard method for array or Traversable data
 */
trait Array_Or_Traversable_Guard_Trait
{
    /**
     * Verifies that the data is an array or Traversable
     *
     * @param mixed  $data           the data to verify
     * @param string $dataName       the data name
     * @param string $exceptionClass FQCN for the exception
     * @return void
     * @throws Exception
     */
    protected function guard_for_array_or_traversable(mixed $data, string $data_name = 'Argument', $exception_class = InvalidArgumentException::class)
    {
        if (!is_array($data) && !$data instanceof Traversable) {
            $message = sprintf('%s must be an array or Traversable, [%s] given', $data_name, get_debug_type($data));
            throw new $exception_class($message);
        }
    }
}