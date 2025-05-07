<?php

namespace App\Services\Fixtures\Strategies;

interface FixtureStrategyInterface
{
    public function generateFixtures(array $teams): array;
}