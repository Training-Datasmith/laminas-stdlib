<?php

declare(strict_types=1);

namespace Laminas\Stdlib;

use function is_array;

use ReturnTypeWillChange;
use Serializable;

use function serialize;
use function sprintf;

use UnexpectedValueException;

use function unserialize;

/**
 * Serializable version of SplStack
 *
 * @template TValue
 * @extends \SplStack<TValue>
 */
class SplStack extends \SplStack implements Serializable
{
    /**
     * Serialize to an array representing the stack
     *
     * @return list<TValue>
     */
    public function toArray(): array
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
    #[ReturnTypeWillChange]
    public function serialize()
    {
        return serialize($this->__serialize());
    }

    /**
     * Magic method used for serializing of an instance.
     *
     * @return list<TValue>
     */
    #[ReturnTypeWillChange]
    public function __serialize()
    {
        return $this->toArray();
    }

    /**
     * Unserialize
     *
     * @param  string $data
     */
    #[ReturnTypeWillChange]
    public function unserialize($data): void
    {
        $toUnserialize = unserialize($data, ['allowed_classes' => [self::class]]);
        if (! is_array($toUnserialize)) {
            throw new UnexpectedValueException(sprintf(
                'Cannot deserialize %s instance; corrupt serialization data',
                self::class
            ));
        }

        $this->__unserialize($toUnserialize);
    }

    /**
     * Magic method used to rebuild an instance.
     *
     * @param array<array-key, TValue> $data Data array.
     * @return void
     */
    #[ReturnTypeWillChange]
    public function __unserialize($data)
    {
        foreach ($data as $item) {
            $this->unshift($item);
        }
    }
}
