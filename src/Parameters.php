<?php

declare (strict_types=1);
namespace Laminas\Stdlib;

use ArrayObject as PhpArrayObject;
use function http_build_query;
use function parse_str;
use Return_Type_Will_Change;
/**
 * @template TKey of array-key
 * @template TValue
 * @template-extends PhpArrayObject<TKey, TValue>
 * @template-implements ParametersInterface<TKey, TValue>
 */
class Parameters extends Php_Array_Object implements Parameters_Interface
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
    public function from_array(array $values): void
    {
        $this->exchange_array($values);
    }
    /**
     * Populate from query string
     *
     * @param  string $string
     */
    public function from_string($string): void
    {
        $array = [];
        parse_str($string, $array);
        $this->from_array($array);
    }
    /**
     * Serialize to native PHP array
     *
     * @return array<TKey, TValue>
     */
    public function to_array(): array
    {
        return $this->get_array_copy();
    }
    /**
     * Serialize to query string
     */
    public function to_string(): string
    {
        return http_build_query($this->to_array());
    }
    /**
     * Retrieve by key
     *
     * Returns null if the key does not exist.
     *
     * @param  TKey $name
     * @return TValue|null
     */
    #[Return_Type_Will_Change]
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