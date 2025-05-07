<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Teams\TeamsService;

class TeamsServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('teams.service', function () {
            return new TeamsService();
        });
    }

    public function boot()
    {
        //
    }
}