<?php

declare(strict_types=1);

namespace LaminasTest\Stdlib\StaticAnalysis;

use function array_values;

use function iterator_to_array;

use Laminas\Stdlib\SplPriorityQueue;

final class SplPriorityQueueGenericsCanBeUnderstood
{
    /**
     * @param SplPriorityQueue<string, int> $laminas
     */
    public function __construct(private SplPriorityQueue $laminas)
    {
    }

    /** @return list<string> */
    public function laminasList(): array
    {
        return array_values(iterator_to_array($this->laminas));
    }
}
