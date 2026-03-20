<?php

declare (strict_types=1);
namespace Laminas\Stdlib;

use function is_array;
use Return_Type_Will_Change;
use Serializable;
use function serialize;
use function sprintf;
use UnexpectedValueException;
use function unserialize;
/**
 * Serializable version of SplQueue
 *
 * @template TKey of array-key
 * @template TValue
 * @extends \SplQueue<TValue>
 */
class SplQueue extends \SplQueue implements Serializable
{
    /**
     * Return an array representing the queue
     *
     * @return list<TValue>
     */
    public function to_array(): array
    {
        $array = [];
        foreach ($this as $item) {
            $array[] = $item;
        }
        return $array;
    }
    /**
     * Serialize
     *
     * @return string
     */
    #[Return_Type_Will_Change]
    public function serialize()
    {
        return serialize($this->__serialize());
    }
    /**
     * Magic method used for serializing of an instance.
     *
     * @return list<TValue>
     */
    #[Return_Type_Will_Change]
    public function __serialize()
    {
        return $this->to_array();
    }
    /**
     * Unserialize
     *
     * @param  string $data
     */
    #[Return_Type_Will_Change]
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
     * @param array<array-key, TValue> $data Data array.
     * @return void
     */
    #[Return_Type_Will_Change]
    public function __unserialize($data)
    {
        foreach ($data as $item) {
            $this->push($item);
        }
    }
}