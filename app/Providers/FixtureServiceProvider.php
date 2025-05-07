<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Fixtures\FixtureService;

class FixtureServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton('fixture.service', function () {
            return new FixtureService();
        });
    }

    public function boot()
    {
        //
    }
}