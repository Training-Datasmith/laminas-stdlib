<?php

declare (strict_types=1);
namespace Laminas\Stdlib;

use function array_map;
use Countable;
use function current;
use Exception;
use Iterator;
use function key;
use function next;
use function reset;
use Return_Type_Will_Change;
use function uasort;
/**
 * @template TKey of string
 * @template TValue of mixed
 * @template-implements Iterator<TKey, TValue>
 */
class Priority_List implements Iterator, Countable
{
    public const EXTR_DATA = 0x1;
    public const EXTR_PRIORITY = 0x2;
    public const EXTR_BOTH = 0x3;
    /**
     * Internal list of all items.
     *
     * @var array<TKey, array{data: TValue, priority: int, serial: positive-int|0}>
     */
    protected $items = [];
    /**
     * Serial assigned to items to preserve LIFO.
     *
     * @var positive-int|0
     */
    protected $serial = 0;
    // phpcs:disable WebimpressCodingStandard.NamingConventions.ValidVariableName.NotCamelCapsProperty
    /**
     * Serial order mode
     *
     * @var integer
     */
    protected $is_lifo = 1;
    // phpcs:enable
    /**
     * Internal counter to avoid usage of count().
     *
     * @var int
     */
    protected $count = 0;
    /**
     * Whether the list was already sorted.
     *
     * @var bool
     */
    protected $sorted = false;
    /**
     * Insert a new item.
     *
     * @param TKey   $name
     * @param TValue $value
     * @param int    $priority
     */
    public function insert($name, mixed $value, $priority = 0): void
    {
        if (!isset($this->items[$name])) {
            $this->count++;
        }
        $this->sorted = false;
        $this->items[$name] = ['data' => $value, 'priority' => (int) $priority, 'serial' => $this->serial++];
    }
    /**
     * @param TKey   $name
     * @param int    $priority
     * @return $this
     * @throws Exception
     */
    public function set_priority($name, $priority): static
    {
        if (!isset($this->items[$name])) {
            throw new Exception("item {$name} not found");
        }
        $this->items[$name]['priority'] = (int) $priority;
        $this->sorted = false;
        return $this;
    }
    /**
     * Remove a item.
     *
     * @param  TKey $name
     */
    public function remove($name): void
    {
        if (isset($this->items[$name])) {
            $this->count--;
        }
        unset($this->items[$name]);
    }
    /**
     * Remove all items.
     */
    public function clear(): void
    {
        $this->items = [];
        $this->serial = 0;
        $this->count = 0;
        $this->sorted = false;
    }
    /**
     * Get a item.
     *
     * @param  TKey $name
     * @return TValue|null
     */
    public function get($name)
    {
        if (!isset($this->items[$name])) {
            return;
        }
        return $this->items[$name]['data'];
    }
    /**
     * Sort all items.
     *
     * @return void
     */
    protected function sort()
    {
        if (!$this->sorted) {
            uasort($this->items, $this->compare(...));
            $this->sorted = true;
        }
    }
    /**
     * Compare the priority of two items.
     *
     * @param  array $item1,
     * @return int
     */
    protected function compare(array $item1, array $item2): int|float
    {
        return $item1['priority'] === $item2['priority'] ? ($item1['serial'] > $item2['serial'] ? -1 : 1) * $this->is_lifo : ($item1['priority'] > $item2['priority'] ? -1 : 1);
    }
    /**
     * Get/Set serial order mode
     *
     * @param bool|null $flag
     */
    public function is_lifo($flag = null): bool
    {
        if ($flag !== null) {
            $is_lifo = $flag === true ? 1 : -1;
            if ($is_lifo !== $this->is_lifo) {
                $this->is_lifo = $is_lifo;
                $this->sorted = false;
            }
        }
        return 1 === $this->is_lifo;
    }
    /**
     * {@inheritDoc}
     */
    #[Return_Type_Will_Change]
    public function rewind(): void
    {
        $this->sort();
        reset($this->items);
    }
    /**
     * {@inheritDoc}
     */
    #[Return_Type_Will_Change]
    public function current()
    {
        $this->sorted || $this->sort();
        $node = current($this->items);
        return $node ? $node['data'] : false;
    }
    /**
     * {@inheritDoc}
     */
    #[Return_Type_Will_Change]
    public function key()
    {
        $this->sorted || $this->sort();
        return key($this->items);
    }
    /**
     * {@inheritDoc}
     */
    #[Return_Type_Will_Change]
    public function next()
    {
        $node = next($this->items);
        return $node ? $node['data'] : false;
    }
    /**
     * {@inheritDoc}
     */
    #[Return_Type_Will_Change]
    public function valid()
    {
        return current($this->items) !== false;
    }
    public function getIterator(): static
    {
        return clone $this;
    }
    /**
     * {@inheritDoc}
     */
    #[Return_Type_Will_Change]
    public function count()
    {
        return $this->count;
    }
    /**
     * Return list as array
     *
     * @param int $flag
     * @return array
     */
    public function to_array($flag = self::EXTR_DATA)
    {
        $this->sort();
        if ($flag === self::EXTR_BOTH) {
            return $this->items;
        }
        return array_map(static fn(array $item) => $flag === self::EXTR_PRIORITY ? $item['priority'] : $item['data'], $this->items);
    }
}