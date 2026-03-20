<?php

declare (strict_types=1);
namespace Laminas\Stdlib\Array_Utils;

final class Merge_Replace_Key implements Merge_Replace_Key_Interface
{
    public function __construct(protected mixed $data)
    {
    }
    /**
     * {@inheritDoc}
     */
    public function get_data()
    {
        return $this->data;
    }
}