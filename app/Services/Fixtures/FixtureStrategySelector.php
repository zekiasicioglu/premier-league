<?php

namespace App\Services\Fixtures;

use App\Services\Fixtures\Strategies\FixtureStrategyInterface;
use App\Services\Fixtures\Strategies\RoundRobinStrategy;
use App\Services\Fixtures\Strategies\SwissStrategy;

class FixtureStrategySelector
{
    public static function getStrategy(string $type): FixtureStrategyInterface
    {
        return match ($type) {
            'round-robin' => new RoundRobinStrategy(),
            'swiss' => new SwissStrategy(),
            default => throw new \InvalidArgumentException("Unknown strategy: $type"),
        };
    }
}