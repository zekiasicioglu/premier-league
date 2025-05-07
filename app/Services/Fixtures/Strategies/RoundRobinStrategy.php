<?php

namespace App\Services\Fixtures\Strategies;

class RoundRobinStrategy implements FixtureStrategyInterface
{
    public function generateFixtures(array $teams): array
    {
        $fixtures = [];
        $numTeams = count($teams);

        for ($round = 1; $round < $numTeams; $round++) {
            for ($i = 0; $i < $numTeams / 2; $i++) {
                $home = $teams[$i];
                $away = $teams[$numTeams - 1 - $i];
                
                $fixtures[] = [
                    'week' => $round,
                    'home_team_id' => $home,
                    'away_team_id' => $away,
                ];

                $fixtures[] = [
                    'week' => $round + $numTeams - 1,
                    'home_team_id' => $away,
                    'away_team_id' => $home,
                ];
            }
            array_splice($teams, 1, 0, array_pop($teams));
        }

        return $fixtures;
    }
}