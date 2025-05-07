<?php

namespace App\Services\Teams\Strategies;

interface TeamsStrategyInterface
{
    public function fetchTeams(): array;
}