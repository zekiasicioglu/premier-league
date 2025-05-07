<?php

namespace App\Services\Fixtures\Strategies;

class SwissStrategy implements FixtureStrategyInterface
{
    public function generateFixtures(array $teams): array
    {
        $fixtures = [];
        $numRounds = count($teams) - 1;

        shuffle($teams);

        for ($round = 1; $round <= $numRounds; $round++) {
            $pairs = [];
            $available = $teams;

            while (count($available) > 1) {
                $home = array_shift($available);
                $away = array_shift($available);

                $fixtures[] = [
                    'week' => $round,
                    'home_team_id' => $home,
                    'away_team_id' => $away,
                ];
            }
        }

        return $fixtures;
    }
}