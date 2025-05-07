<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class FixtureServiceFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'fixture.service';
    }
}