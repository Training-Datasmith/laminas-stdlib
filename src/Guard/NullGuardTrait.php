<?php

declare (strict_types=1);
namespace Laminas\Stdlib\Guard;

use Exception;
use Laminas\Stdlib\Exception\InvalidArgumentException;
use function sprintf;
/**
 * Provide a guard method against null data
 */
trait Null_Guard_Trait
{
    /**
     * Verify that the data is not null
     *
     * @param mixed  $data           the data to verify
     * @param string $dataName       the data name
     * @param string $exceptionClass FQCN for the exception
     * @return void
     * @throws Exception
     */
    protected function guard_against_null(mixed $data, string $data_name = 'Argument', $exception_class = InvalidArgumentException::class)
    {
        if (null === $data) {
            $message = sprintf('%s cannot be null', $data_name);
            throw new $exception_class($message);
        }
    }
}