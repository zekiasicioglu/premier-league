<?php

namespace App\Services\Teams;

use App\Models\Team;
use App\Services\Teams\Strategies\TeamsStrategyInterface;

class TeamsService
{
    private TeamsStrategyInterface $strategy;

    public function setStrategy(string $type): void
    {
        $this->strategy = TeamsStrategySelector::getStrategy($type);
    }

    public function getStrategy(): ?TeamsStrategyInterface
    {
        return $this->strategy;
    }

    public function seedTeams(?string $strategyType = null): bool
    {
        if(!empty($strategyType)){
            $this->setStrategy($strategyType);
        }
        if(empty($this->strategy)){
            throw new \InvalidArgumentException("Missing strategy");
        }
        
        $teams = $this->strategy->fetchTeams();

        if (empty($teams)) {
            return false;
        }
        Team::query()->delete();
        Team::insert($teams);
        
        return true;    
    }
}