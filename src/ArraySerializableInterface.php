<?php

declare (strict_types=1);
namespace Laminas\Stdlib;

interface Array_Serializable_Interface
{
    /**
     * Exchange internal values from provided array
     *
     * @return void
     */
    public function exchange_array(array $array);
    /**
     * Return an array representation of the object
     *
     * @return array
     */
    public function get_array_copy();
}