<?php

declare (strict_types=1);
namespace Laminas\Stdlib;

use Traversable;
interface Message_Interface
{
    /**
     * Set metadata
     *
     * @param string|int|array|Traversable $spec
     * @param  mixed $value
     * @return $this
     */
    public function set_metadata($spec, $value = null);
    /**
     * Get metadata
     *
     * @param  null|string|int $key
     * @return mixed
     */
    public function get_metadata($key = null);
    /**
     * Set content
     *
     * @param  mixed $content
     * @return mixed
     */
    public function set_content($content);
    /**
     * Get content
     *
     * @return mixed
     */
    public function get_content();
}