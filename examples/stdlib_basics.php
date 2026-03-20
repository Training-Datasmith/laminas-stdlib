<?php

declare(strict_types=1);

/**
 * Example: Priority_Queue, AbstractOptions, and ArrayUtils from laminas-stdlib.
 *
 * Run from the laminas-stdlib project root:
 *   php examples/stdlib_basics.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Laminas\Stdlib\SplPriorityQueue;
use Laminas\Stdlib\ArrayUtils;

// --- SplPriorityQueue (stable: FIFO within same priority) ---
$queue = new SplPriorityQueue();
$queue->insert('low task A',    1);
$queue->insert('high task B',  10);
$queue->insert('high task C',  10);  // same priority as B — should come after B (FIFO)
$queue->insert('medium task D', 5);

echo "Queue order (highest priority first):\n";
foreach (clone $queue as $item) {
    echo "  - $item\n";
}

echo "\n";

// --- ArrayUtils::merge: recursive merge with numeric key re-indexing ---
$defaults = [
    'debug'  => false,
    'cache'  => ['driver' => 'file', 'ttl' => 3600],
    'tags'   => ['php', 'laminas'],
];
$overrides = [
    'debug'  => true,
    'cache'  => ['ttl' => 60],
    'tags'   => ['override'],
];

$merged = ArrayUtils::merge($defaults, $overrides);

echo "Merged config:\n";
echo "  debug:       " . var_export($merged['debug'], true) . "\n";
echo "  cache.driver: " . $merged['cache']['driver'] . "\n";
echo "  cache.ttl:   " . $merged['cache']['ttl'] . "\n";
echo "  tags:        " . implode(', ', $merged['tags']) . "\n\n";

// --- ArrayUtils::isHashTable ---
$hash  = ['foo' => 1, 'bar' => 2];
$list  = [1, 2, 3];
$mixed = [0 => 'a', 1 => 'b', 'c' => 'd'];

printf("isHashTable([foo,bar]): %s\n", ArrayUtils::isHashTable($hash)  ? 'yes' : 'no');
printf("isHashTable([1,2,3]):  %s\n", ArrayUtils::isHashTable($list)  ? 'yes' : 'no');
printf("isHashTable(mixed):    %s\n", ArrayUtils::isHashTable($mixed) ? 'yes' : 'no');
