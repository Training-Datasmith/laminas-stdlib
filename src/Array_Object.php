<?php

declare (strict_types=1);
namespace Laminas\Stdlib;

use Allow_Dynamic_Properties;
use function array_key_exists;
use function array_keys;
use ArrayAccess;
use ArrayIterator;
use function asort;
use function class_exists;
use function count;
use Countable;
use const E_USER_DEPRECATED;
use function get_debug_type;
use function get_object_vars;
use function gettype;
use function in_array;
use function is_array;
use function is_callable;
use function is_object;
use function is_string;
use Iterator;
use IteratorAggregate;
use function ksort;
use function natcasesort;
use function natsort;
use Return_Type_Will_Change;
use Serializable;
use function serialize;
use function sprintf;
use function str_starts_with;
use function trigger_error;
use function uasort;
use function uksort;
use UnexpectedValueException;
use function unserialize;
/**
 * Custom framework ArrayObject implementation
 *
 * Extends version-specific "abstract" implementation.
 *
 * @template TKey of array-key
 * @template TValue
 * @template-implements IteratorAggregate<TKey, TValue>
 * @template-implements ArrayAccess<TKey, TValue>
 * @psalm-no-seal-properties
 */
#[Allow_Dynamic_Properties]
class ArrayObject implements IteratorAggregate, ArrayAccess, Serializable, Countable
{
    /**
     * Properties of the object have their normal functionality
     * when accessed as list (var_dump, foreach, etc.).
     */
    public const STD_PROP_LIST = 1;
    /**
     * Entries can be accessed as properties (read and write).
     */
    public const ARRAY_AS_PROPS = 2;
    /** @var self::STD_PROP_LIST|self::ARRAY_AS_PROPS */
    protected $flag;
    /** @var class-string<Iterator> */
    protected $iterator_class;
    /** @var list<string> */
    protected array $protected_properties;
    /**
     * @param array<TKey, TValue>|object $storage Object values must act like ArrayAccess
     * @param self::STD_PROP_LIST|self::ARRAY_AS_PROPS $flags
     * @param class-string<Iterator>                   $iteratorClass
     */
    public function __construct(protected $storage = [], $flags = self::STD_PROP_LIST, $iterator_class = ArrayIterator::class)
    {
        $this->set_flags($flags);
        $this->set_iterator_class($iterator_class);
        $this->protected_properties = array_keys(get_object_vars($this));
    }
    /**
     * Returns whether the requested key exists
     *
     * @param TKey $key
     * @return bool
     */
    public function __isset(mixed $key)
    {
        if ($this->flag === self::ARRAY_AS_PROPS) {
            return $this->offsetExists($key);
        }
        if (in_array($key, $this->protected_properties)) {
            throw new Exception\InvalidArgumentException("{$key} is a protected property, use a different key");
        }
        return isset($this->{$key});
    }
    /**
     * Sets the value at the specified key to value
     *
     * @param TKey $key
     * @param TValue $value
     * @return void
     */
    public function __set(mixed $key, mixed $value)
    {
        if ($this->flag === self::ARRAY_AS_PROPS) {
            $this->offsetSet($key, $value);
            return;
        }
        if (in_array($key, $this->protected_properties)) {
            throw new Exception\InvalidArgumentException("{$key} is a protected property, use a different key");
        }
        $this->{$key} = $value;
    }
    /**
     * Unsets the value at the specified key
     *
     * @param TKey $key
     * @return void
     */
    public function __unset(mixed $key)
    {
        if ($this->flag === self::ARRAY_AS_PROPS) {
            $this->offsetUnset($key);
            return;
        }
        if (in_array($key, $this->protected_properties)) {
            throw new Exception\InvalidArgumentException("{$key} is a protected property, use a different key");
        }
        unset($this->{$key});
    }
    /**
     * Returns the value at the specified key by reference
     *
     * @param TKey $key
     * @return TValue|null
     */
    public function &__get(mixed $key): mixed
    {
        if ($this->flag === self::ARRAY_AS_PROPS) {
            $ret =& $this->offsetGet($key);
            return $ret;
        }
        if (in_array($key, $this->protected_properties, true)) {
            throw new Exception\InvalidArgumentException("{$key} is a protected property, use a different key");
        }
        return $this->{$key};
    }
    /**
     * Appends the value
     *
     * @param TValue $value
     */
    public function append(mixed $value): void
    {
        $this->storage[] = $value;
    }
    /**
     * Sort the entries by value
     */
    public function asort(): void
    {
        asort($this->storage);
    }
    /**
     * Get the number of public properties in the ArrayObject
     *
     * @return positive-int|0
     */
    #[Return_Type_Will_Change]
    public function count()
    {
        return count($this->storage);
    }
    /**
     * Exchange the array for another one.
     *
     * Passing non-array values is deprecated and the next major version will add an array type hint
     *
     * @param array<TKey, TValue>|ArrayObject<TKey, TValue>|ArrayIterator<TKey, TValue>|object $data
     * @return array<TKey, TValue>
     */
    public function exchange_array($data)
    {
        if (!is_array($data) && !is_object($data)) {
            throw new Exception\InvalidArgumentException('Passed variable is not an array or object, using empty array instead');
        }
        if (!is_array($data)) {
            trigger_error(sprintf('Passing a non-array value to "%s" is deprecated and will be removed in the next major version', __METHOD__), E_USER_DEPRECATED);
        }
        if ($data instanceof self || $data instanceof \ArrayObject) {
            $data = $data->get_array_copy();
        }
        if (!is_array($data)) {
            $data = (array) $data;
        }
        $storage = $this->storage;
        $this->storage = $data;
        return $storage;
    }
    /**
     * Creates a copy of the ArrayObject.
     *
     * @return array<TKey, TValue>
     */
    public function get_array_copy()
    {
        return $this->storage;
    }
    /**
     * Gets the behavior flags.
     *
     * @return self::STD_PROP_LIST|self::ARRAY_AS_PROPS
     */
    public function get_flags()
    {
        return $this->flag;
    }
    /**
     * Create a new iterator from an ArrayObject instance
     *
     * @return Iterator<TKey, TValue>
     */
    #[Return_Type_Will_Change]
    public function getIterator()
    {
        $class = $this->iterator_class;
        return new $class($this->storage);
    }
    /**
     * Gets the iterator classname for the ArrayObject.
     *
     * @return class-string<Iterator>
     */
    public function get_iterator_class()
    {
        return $this->iterator_class;
    }
    /**
     * Sort the entries by key
     */
    public function ksort(): void
    {
        ksort($this->storage);
    }
    /**
     * Sort an array using a case insensitive "natural order" algorithm
     */
    public function natcasesort(): void
    {
        natcasesort($this->storage);
    }
    /**
     * Sort entries using a "natural order" algorithm
     */
    public function natsort(): void
    {
        natsort($this->storage);
    }
    /**
     * Returns whether the requested key exists
     *
     * @param TKey $key
     * @return bool
     */
    #[Return_Type_Will_Change]
    public function offsetExists(mixed $key)
    {
        return isset($this->storage[$key]);
    }
    /**
     * {@inheritDoc}
     *
     * @param TKey $key
     * @return TValue|null
     */
    #[Return_Type_Will_Change]
    public function &offsetGet(mixed $key)
    {
        $ret = null;
        if (!$this->offsetExists($key)) {
            return $ret;
        }
        $ret =& $this->storage[$key];
        return $ret;
    }
    /**
     * Sets the value at the specified key to value
     *
     * @param TKey $offset
     * @param TValue $value
     */
    #[Return_Type_Will_Change]
    public function offsetSet(mixed $offset, mixed $value): void
    {
        $this->storage[$offset] = $value;
    }
    /**
     * Unsets the value at the specified key
     *
     * @param TKey $offset
     */
    #[Return_Type_Will_Change]
    public function offsetUnset(mixed $offset): void
    {
        if ($this->offsetExists($offset)) {
            unset($this->storage[$offset]);
        }
    }
    /**
     * Serialize an ArrayObject
     *
     * @return string
     */
    public function serialize()
    {
        return serialize($this->__serialize());
    }
    /**
     * Magic method used for serializing of an instance.
     *
     * @return array<string, mixed>
     */
    public function __serialize()
    {
        return get_object_vars($this);
    }
    /**
     * Sets the behavior flags
     *
     * @param self::STD_PROP_LIST|self::ARRAY_AS_PROPS $flags
     */
    public function set_flags($flags): void
    {
        $this->flag = $flags;
    }
    /**
     * Sets the iterator classname for the ArrayObject
     *
     * @param  class-string<Iterator> $class
     */
    public function set_iterator_class($class): void
    {
        if (class_exists($class)) {
            $this->iterator_class = $class;
            return;
        }
        if (str_starts_with($class, '\\')) {
            $class = '\\' . $class;
            if (class_exists($class)) {
                $this->iterator_class = $class;
                return;
            }
        }
        throw new Exception\InvalidArgumentException('The iterator class does not exist');
    }
    /**
     * Sort the entries with a user-defined comparison function and maintain key association
     *
     * @param  callable(TValue, TValue): int $function
     */
    public function uasort($function): void
    {
        if (is_callable($function)) {
            uasort($this->storage, $function);
        }
    }
    /**
     * Sort the entries by keys using a user-defined comparison function
     *
     * @param  callable(TKey, TKey): int $function
     */
    public function uksort($function): void
    {
        if (is_callable($function)) {
            uksort($this->storage, $function);
        }
    }
    /**
     * Unserialize an ArrayObject
     *
     * @param  string $data
     */
    public function unserialize($data): void
    {
        $to_unserialize = unserialize($data, ['allowed_classes' => [self::class]]);
        if (!is_array($to_unserialize)) {
            throw new UnexpectedValueException(sprintf('Cannot deserialize %s instance; corrupt serialization data', self::class));
        }
        $this->__unserialize($to_unserialize);
    }
    /**
     * Magic method used to rebuild an instance.
     *
     * @param array $data Data array.
     * @return void
     */
    public function __unserialize(array $data)
    {
        $this->protected_properties = array_keys(get_object_vars($this));
        // Unserialize protected internal properties first
        if (array_key_exists('flag', $data)) {
            $this->set_flags((int) $data['flag']);
            unset($data['flag']);
        }
        if (array_key_exists('storage', $data)) {
            if (!is_array($data['storage']) && !is_object($data['storage'])) {
                throw new UnexpectedValueException(sprintf('Cannot deserialize %s instance: corrupt storage data; expected array or object, received %s', self::class, gettype($data['storage'])));
            }
            $this->exchange_array($data['storage']);
            unset($data['storage']);
        }
        if (array_key_exists('iteratorClass', $data)) {
            if (!is_string($data['iteratorClass'])) {
                throw new UnexpectedValueException(sprintf('Cannot deserialize %s instance: invalid iteratorClass; expected string, received %s', self::class, get_debug_type($data['iteratorClass'])));
            }
            $this->set_iterator_class($data['iteratorClass']);
            unset($data['iteratorClass']);
        }
        unset($data['protectedProperties']);
        // Unserialize array keys after resolving protected properties to ensure configuration is used.
        foreach ($data as $k => $v) {
            $this->__set($k, $v);
        }
    }
}