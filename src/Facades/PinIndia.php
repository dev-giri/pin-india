<?php

namespace PinIndia\Facades;

use Illuminate\Support\Facades\Facade;

class PinIndia extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'pinindia';
    }
}
