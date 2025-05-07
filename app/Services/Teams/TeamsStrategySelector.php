<?php

namespace App\Services\Teams;

use App\Services\Teams\Strategies\TeamsStrategyInterface;
use App\Services\Teams\Strategies\TeamsFromTeamsPage;
use App\Services\Teams\Strategies\TeamsFromTablesPage;

class TeamsStrategySelector
{
    public static function getStrategy(string $type): TeamsStrategyInterface
    {
        return match ($type) {
            'teams' => new TeamsFromTeamsPage(),
            'tables' => new TeamsFromTablesPage(),
            default => throw new \InvalidArgumentException("Unknown strategy: $type"),
        };
    }
}