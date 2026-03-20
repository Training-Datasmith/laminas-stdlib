<?php

declare (strict_types=1);
namespace Laminas\Stdlib;

use function array_reverse;
use ArrayIterator;
use ArrayObject as PhpArrayObject;
use Return_Type_Will_Change;
/**
 * ArrayObject that acts as a stack with regards to iteration
 *
 * @template TKey of array-key
 * @template TValue
 * @template-extends PhpArrayObject<TKey, TValue>
 */
class Array_Stack extends Php_Array_Object
{
    /**
     * Retrieve iterator
     *
     * Retrieve an array copy of the object, reverse its order, and return an
     * ArrayIterator with that reversed array.
     *
     * @return ArrayIterator<TKey, TValue>
     */
    #[Return_Type_Will_Change]
    public function getIterator()
    {
        $array = $this->get_array_copy();
        return new ArrayIterator(array_reverse($array));
    }
}