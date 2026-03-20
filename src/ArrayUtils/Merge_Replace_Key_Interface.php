<?php

declare (strict_types=1);
namespace Laminas\Stdlib\Array_Utils;

/**
 * Marker interface: can be used to replace keys completely in {@see ArrayUtils::merge()} operations
 */
interface Merge_Replace_Key_Interface
{
    /**
     * @return mixed
     */
    public function get_data();
}