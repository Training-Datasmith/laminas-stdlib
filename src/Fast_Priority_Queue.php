<?php

declare (strict_types=1);
namespace Laminas\Stdlib;

use Countable;
use function current;
use function in_array;
use function is_array;
use function is_int;
use Iterator;
use function key;
use function max;
use function next;
use function reset;
use Return_Type_Will_Change;
use Serializable;
use function serialize;
use SplPriorityQueue as PhpSplPriorityQueue;
use function sprintf;
use UnexpectedValueException;
use function unserialize;
/**
 * This is an efficient implementation of an integer priority queue in PHP
 *
 * This class acts like a queue with insert() and extract(), removing the
 * elements from the queue and it also acts like an Iterator without removing
 * the elements. This behaviour can be used in mixed scenarios with high
 * performance boost.
 *
 * @template TValue of mixed
 * @template-implements Iterator<int, TValue>
 */
class Fast_Priority_Queue implements Iterator, Countable, Serializable
{
    public const EXTR_DATA = Php_Spl_Priority_Queue::EXTR_DATA;
    public const EXTR_PRIORITY = Php_Spl_Priority_Queue::EXTR_PRIORITY;
    public const EXTR_BOTH = Php_Spl_Priority_Queue::EXTR_BOTH;
    /** @var self::EXTR_* */
    protected $extract_flag = self::EXTR_DATA;
    /**
     * Elements of the queue, divided by priorities
     *
     * @var array<int, list<TValue>>
     */
    protected $values = [];
    /**
     * Array of priorities
     *
     * @var array<int, int>
     */
    protected $priorities = [];
    /**
     * Array of priorities used for the iteration
     *
     * @var array
     */
    protected $sub_priorities = [];
    /**
     * Max priority
     *
     * @var int|null
     */
    protected $max_priority;
    /**
     * Total number of elements in the queue
     *
     * @var int
     */
    protected $count = 0;
    /**
     * Index of the current element in the queue
     *
     * @var int
     */
    protected $index = 0;
    /**
     * Sub index of the current element in the same priority level
     *
     * @var int
     */
    protected $sub_index = 0;
    public function __serialize(): array
    {
        $clone = clone $this;
        $clone->set_extract_flags(self::EXTR_BOTH);
        $data = [];
        foreach ($clone as $item) {
            $data[] = $item;
        }
        return $data;
    }
    public function __unserialize(array $data): void
    {
        foreach ($data as $item) {
            $this->insert($item['data'], $item['priority']);
        }
    }
    /**
     * Insert an element in the queue with a specified priority
     *
     * @param TValue $value
     * @param int    $priority
     */
    public function insert(mixed $value, $priority): void
    {
        if (!is_int($priority)) {
            throw new Exception\InvalidArgumentException('The priority must be an integer');
        }
        $this->values[$priority][] = $value;
        if (!isset($this->priorities[$priority])) {
            $this->priorities[$priority] = $priority;
            $this->max_priority = $this->max_priority === null ? $priority : max($priority, $this->max_priority);
        }
        ++$this->count;
    }
    /**
     * Extract an element in the queue according to the priority and the
     * order of insertion
     *
     * @return TValue|int|array{data: TValue, priority: int}|false
     */
    public function extract()
    {
        if (!$this->valid()) {
            return false;
        }
        $value = $this->current();
        $this->next_and_remove();
        return $value;
    }
    /**
     * Remove an item from the queue
     *
     * This is different than {@link extract()}; its purpose is to dequeue an
     * item.
     *
     * Note: this removes the first item matching the provided item found. If
     * the same item has been added multiple times, it will not remove other
     * instances.
     *
     * @return bool False if the item was not found, true otherwise.
     */
    public function remove(mixed $datum): bool
    {
        $current_index = $this->index;
        $current_sub_index = $this->sub_index;
        $current_priority = $this->max_priority;
        $this->rewind();
        while ($this->valid()) {
            if (current($this->values[$this->max_priority]) === $datum) {
                $index = key($this->values[$this->max_priority]);
                unset($this->values[$this->max_priority][$index]);
                // The `next()` method advances the internal array pointer, so we need to use the `reset()` function,
                // otherwise we would lose all elements before the place the pointer points.
                reset($this->values[$this->max_priority]);
                $this->index = $current_index;
                $this->sub_index = $current_sub_index;
                // If the array is empty we need to destroy the unnecessary priority,
                // otherwise we would end up with an incorrect value of `$this->count`
                // {@see \Laminas\Stdlib\FastPriorityQueue::nextAndRemove()}.
                if (empty($this->values[$this->max_priority])) {
                    unset($this->values[$this->max_priority]);
                    unset($this->priorities[$this->max_priority]);
                    if ($this->max_priority === $current_priority) {
                        $this->sub_index = 0;
                    }
                }
                $this->max_priority = empty($this->priorities) ? null : max($this->priorities);
                --$this->count;
                return true;
            }
            $this->next();
        }
        return false;
    }
    /**
     * Get the total number of elements in the queue
     *
     * @return int
     */
    #[Return_Type_Will_Change]
    public function count()
    {
        return $this->count;
    }
    /**
     * Get the current element in the queue
     *
     * @return TValue|int|array{data: TValue|false, priority: int|null}|false
     */
    #[Return_Type_Will_Change]
    public function current()
    {
        switch ($this->extract_flag) {
            case self::EXTR_DATA:
                return current($this->values[$this->max_priority]);
            case self::EXTR_PRIORITY:
                return $this->max_priority;
            case self::EXTR_BOTH:
                return ['data' => current($this->values[$this->max_priority]), 'priority' => $this->max_priority];
        }
    }
    /**
     * Get the index of the current element in the queue
     *
     * @return int
     */
    #[Return_Type_Will_Change]
    public function key()
    {
        return $this->index;
    }
    /**
     * Set the iterator pointer to the next element in the queue
     * removing the previous element
     *
     * @return void
     */
    protected function next_and_remove()
    {
        $key = key($this->values[$this->max_priority]);
        if (false === next($this->values[$this->max_priority])) {
            unset($this->priorities[$this->max_priority]);
            unset($this->values[$this->max_priority]);
            $this->max_priority = empty($this->priorities) ? null : max($this->priorities);
            $this->sub_index = -1;
        } else {
            unset($this->values[$this->max_priority][$key]);
        }
        ++$this->index;
        ++$this->sub_index;
        --$this->count;
    }
    /**
     * Set the iterator pointer to the next element in the queue
     * without removing the previous element
     */
    #[Return_Type_Will_Change]
    public function next(): void
    {
        if (false === next($this->values[$this->max_priority])) {
            unset($this->sub_priorities[$this->max_priority]);
            reset($this->values[$this->max_priority]);
            $this->max_priority = empty($this->sub_priorities) ? null : max($this->sub_priorities);
            $this->sub_index = -1;
        }
        ++$this->index;
        ++$this->sub_index;
    }
    /**
     * Check if the current iterator is valid
     *
     * @return bool
     */
    #[Return_Type_Will_Change]
    public function valid()
    {
        return isset($this->values[$this->max_priority ?? '']);
    }
    /**
     * Rewind the current iterator
     */
    #[Return_Type_Will_Change]
    public function rewind(): void
    {
        $this->sub_priorities = $this->priorities;
        $this->max_priority = empty($this->priorities) ? 0 : max($this->priorities);
        $this->index = 0;
        $this->sub_index = 0;
    }
    /**
     * Serialize to an array
     *
     * Array will be priority => data pairs
     *
     * @return list<TValue|int|array{data: TValue, priority: int}>
     */
    public function to_array(): array
    {
        $array = [];
        foreach (clone $this as $item) {
            $array[] = $item;
        }
        return $array;
    }
    /**
     * Serialize
     *
     * @return string
     */
    public function serialize()
    {
        return serialize($this->__serialize());
    }
    /**
     * Deserialize
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
     * Set the extract flag
     *
     * @param self::EXTR_* $flag
     */
    public function set_extract_flags($flag): void
    {
        $this->extract_flag = match ($flag) {
            self::EXTR_DATA, self::EXTR_PRIORITY, self::EXTR_BOTH => $flag,
            default => throw new Exception\InvalidArgumentException('The extract flag specified is not valid'),
        };
    }
    /**
     * Check if the queue is empty
     */
    public function is_empty(): bool
    {
        return empty($this->values);
    }
    /**
     * Does the queue contain the given datum?
     */
    public function contains(mixed $datum): bool
    {
        foreach ($this->values as $values) {
            if (in_array($datum, $values)) {
                return true;
            }
        }
        return false;
    }
    /**
     * Does the queue have an item with the given priority?
     */
    public function has_priority(int $priority): bool
    {
        return isset($this->values[$priority]);
    }
}