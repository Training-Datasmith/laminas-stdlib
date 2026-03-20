<?php

declare (strict_types=1);
namespace Laminas\Stdlib\Guard;

/**
 * An aggregate for all guard traits
 */
trait All_Guards_Trait
{
    use Array_Or_Traversable_Guard_Trait;
    use Empty_Guard_Trait;
    use Null_Guard_Trait;
}