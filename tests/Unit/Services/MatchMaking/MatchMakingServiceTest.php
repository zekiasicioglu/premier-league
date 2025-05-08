<?php

namespace Tests\Unit\Services\MatchMaking;

use Tests\TestCase;
use App\Services\MatchMaking\MatchMakingService;
use App\Models\Team;

class MatchMakingServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->matchMakingService = new MatchMakingService();
    }

    public function test_calculate_team_chances_returns_correct_chances()
    {
        $homeTeam = new Team(['strength' => 80]);
        $awayTeam = new Team(['strength' => 20]);

        $chances = $this->matchMakingService->calculateTeamChances($homeTeam, $awayTeam);

        $this->assertIsArray($chances);
        $this->assertArrayHasKey('home', $chances);
        $this->assertArrayHasKey('away', $chances);
        $this->assertEquals(0.8, $chances['home']);
        $this->assertEquals(0.2, $chances['away']);
    }

    public function test_calculate_team_chances_handles_zero_strength()
    {
        $homeTeam = new Team(['strength' => 0]);
        $awayTeam = new Team(['strength' => 0]);

        $chances = $this->matchMakingService->calculateTeamChances($homeTeam, $awayTeam);

        $this->assertEquals(0.5, $chances['home']);
        $this->assertEquals(0.5, $chances['away']);
    }

    public function test_generate_biased_score_returns_valid_score()
    {
        $chance = 0.7;
        $score = $this->matchMakingService->generateBiasedScore($chance);

        $this->assertIsInt($score);
        $this->assertGreaterThanOrEqual(0, $score);
        $this->assertLessThanOrEqual(5, $score);
    }

    public function test_generate_biased_score_with_high_chance()
    {
        $chance = 0.9;
        $score = $this->matchMakingService->generateBiasedScore($chance);

        $this->assertIsInt($score);
        $this->assertGreaterThanOrEqual(0, $score);
        $this->assertLessThanOrEqual(5, $score);
    }

    public function test_generate_biased_score_with_low_chance()
    {
        $chance = 0.1;
        $score = $this->matchMakingService->generateBiasedScore($chance);

        $this->assertIsInt($score);
        $this->assertGreaterThanOrEqual(0, $score);
        $this->assertLessThanOrEqual(5, $score);
    }
} 