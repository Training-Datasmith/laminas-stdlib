<?php

declare (strict_types=1);
namespace Laminas\Stdlib;

interface Dispatchable_Interface
{
    /**
     * Dispatch a request
     *
     * @return Response|mixed
     */
    public function dispatch(Request_Interface $request, ?Response_Interface $response = null);
}