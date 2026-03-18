<?php

declare(strict_types=1);

namespace Laminas\Stdlib;

use ArrayObject as PhpArrayObject;
use ReturnTypeWillChange;

use function http_build_query;
use function parse_str;

/**
 * @template TKey of array-key
 * @template TValue
 * @template-extends PhpArrayObject<TKey, TValue>
 * @template-implements ParametersInterface<TKey, TValue>
 */
class Parameters extends PhpArrayObject implements ParametersInterface
{
    /**
     * Constructor
     *
     * Enforces that we have an array, and enforces parameter access to array
     * elements.
     *
     * @param array<TKey, TValue>|null $values
     */
    public function __construct(?array $values = null)
    {
        if (null === $values) {
            $values = [];
        }
        parent::__construct($values, ArrayObject::ARRAY_AS_PROPS);
    }

    /**
     * Populate from native PHP array
     *
     * @param array<TKey, TValue> $values
     */
    public function fromArray(array $values): void
    {
        $this->exchangeArray($values);
    }

    /**
     * Populate from query string
     *
     * @param  string $string
     */
    public function fromString($string): void
    {
        $array = [];
        parse_str($string, $array);
        $this->fromArray($array);
    }

    /**
     * Serialize to native PHP array
     *
     * @return array<TKey, TValue>
     */
    public function toArray(): array
    {
        return $this->getArrayCopy();
    }

    /**
     * Serialize to query string
     */
    public function toString(): string
    {
        return http_build_query($this->toArray());
    }

    /**
     * Retrieve by key
     *
     * Returns null if the key does not exist.
     *
     * @param  TKey $name
     * @return TValue|null
     */
    #[ReturnTypeWillChange]
    public function offsetGet($name)
    {
        if ($this->offsetExists($name)) {
            return parent::offsetGet($name);
        }

        return null;
    }

    /**
     * @template TDefault
     * @param TKey $name
     * @param TDefault $default optional default value
     * @return TValue|TDefault|null
     */
    public function get($name, $default = null)
    {
        if ($this->offsetExists($name)) {
            return parent::offsetGet($name);
        }
        return $default;
    }

    /**
     * @param TKey   $name
     * @param TValue $value
     * @return $this
     */
    public function set($name, $value): static
    {
        $this[$name] = $value;
        return $this;
    }
}
