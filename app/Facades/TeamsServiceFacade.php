<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class TeamsServiceFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'teams.service';
    }
}