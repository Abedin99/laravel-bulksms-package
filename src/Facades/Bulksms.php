<?php

namespace Abedin99\Bulksms\Facades;

use Illuminate\Support\Facades\Facade;

class Bulksms extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor() { return 'bulksms'; }
}