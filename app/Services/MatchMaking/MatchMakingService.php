<?php

namespace App\Services\MatchMaking;

use App\Models\Team;

class MatchMakingService
{
    public function calculateTeamChances(Team $homeTeam, Team $awayTeam){
        $homeStrength = max(1, $homeTeam->strength);
        $awayStrength = max(1, $awayTeam->strength);
        
        $totalStrength = $homeStrength + $awayStrength;

        if ($totalStrength === 0) {
            $homeChance = 0.5;
            $awayChance = 0.5;
        } else {
            $homeChance = $homeStrength / $totalStrength;
            $awayChance = $awayStrength / $totalStrength;
        }

        return [
            'home' => $homeChance,
            'away' => $awayChance
        ];
    }

    public function generateBiasedScore(float $chance): int
    {
        $maxScore = 5;
        $randomBias = mt_rand(0, 100) / 300.0; 
        $weightedChance = min(1.0, $chance + $randomBias);
        $weightedScore = floor($weightedChance * $maxScore);
    
        return max(0, min(5, (int) $weightedScore));
    }
}