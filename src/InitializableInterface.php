<?php

declare (strict_types=1);
namespace Laminas\Stdlib;

/**
 * Interface to allow objects to have initialization logic
 */
interface Initializable_Interface
{
    /**
     * Init an object
     *
     * @return void
     */
    public function init();
}