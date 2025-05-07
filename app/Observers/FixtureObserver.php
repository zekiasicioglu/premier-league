<?php

namespace App\Observers;

use App\Models\Fixture;
use App\Services\Fixtures\FixtureService;

class FixtureObserver
{
    /**
     * Handle the Fixture "created" event.
     */
    public function created(Fixture $fixture): void
    {
        //
    }
    
    /**
     * Handle the Fixture "updated" event.
     */
    public function updated(Fixture $fixture): void
    {
        if ($fixture->wasChanged('is_played') && $fixture->is_played) {
            FixtureService::updateTeamPoints($fixture->home_team_id, $fixture->home_team_score, $fixture->away_team_score);
            FixtureService::updateTeamPoints($fixture->away_team_id, $fixture->away_team_score, $fixture->home_team_score);
        }
    }

    /**
     * Handle the Fixture "deleted" event.
     */
    public function deleted(Fixture $fixture): void
    {
        //
    }

    /**
     * Handle the Fixture "restored" event.
     */
    public function restored(Fixture $fixture): void
    {
        //
    }

    /**
     * Handle the Fixture "force deleted" event.
     */
    public function forceDeleted(Fixture $fixture): void
    {
        //
    }
}
