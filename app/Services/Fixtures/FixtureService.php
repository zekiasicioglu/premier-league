<?php

namespace App\Services\Fixtures;

use App\Models\Fixture;
use App\Models\Team;
use App\Services\Fixtures\Exceptions\FixtureCompletedException;
use App\Services\Fixtures\Strategies\FixtureStrategyInterface;
use App\Services\Fixtures\FixtureStrategySelector;
use App\Services\MatchMaking\MatchMakingService;

class FixtureService
{
    public FixtureStrategyInterface $strategy;

    public function setStrategy(string $type): void
    {
        $this->strategy = FixtureStrategySelector::getStrategy($type);
    }

    public function getStrategy(): ?FixtureStrategyInterface
    {
        return $this->strategy;
    }

    public function generateFixtures(array $teams, ?string $strategyType = null): array
    {
        if(!empty($strategyType)){
            $this->setStrategy($strategyType);
        }
        if(empty($this->strategy)){
            throw new \InvalidArgumentException("Missing strategy");
        }

        $fixtures = $this->strategy->generateFixtures($teams);

        Fixture::truncate();
        Fixture::insert(
            collect($fixtures)->map(fn($fixture) => [
                'week' => $fixture['week'],
                'home_team_id'=> $fixture['home_team_id'],
                'away_team_id'=> $fixture['away_team_id']
            ])->all()
        );
        return $fixtures;
    }

    public static function getFixtures(): array
    {
        $fixtures = Fixture::with(['home_team:id,name', 'away_team:id,name'])->get();
        $weeks = [];
        $fixtures->each(function ($fixture) use (&$weeks) {
            $weeks[$fixture->week][] = [
                'home_team_id' => $fixture->home_team_id,
                'home_team_name' => $fixture->home_team->name,
                'away_team_id' => $fixture->away_team_id,
                'away_team_name' => $fixture->away_team->name,
            ];
        });

        ksort($weeks);
        $formattedFixtures = collect($weeks)->map(function ($matches, $week) {
            return [
                'week' => (int) $week,
                'matches' => $matches,
            ];
        })->values()->toArray();

        return $formattedFixtures;
    }

    public static function resetFixture(): bool
    {
        Fixture::query()->update([
            'home_team_score' => 0,
            'away_team_score' => 0,
            'is_played' => false,
            'updated_at' => now(),
        ]);
        Team::query()->update([
            'prediction' => 0,
            'played' => 0,
            'won' => 0,
            'drawn' => 0,
            'lost' => 0,
            'goals_for' => 0,
            'goals_against' => 0,
            'goals_difference' => 0,
            'points' => 0,
        ]);

        return true;
    }

    public static function deleteFixture(): bool
    {
        Fixture::truncate();
        Team::query()->update([
            'prediction' => 0,
            'played' => 0,
            'won' => 0,
            'drawn' => 0,
            'lost' => 0,
            'goals_for' => 0,
            'goals_against' => 0,
            'goals_difference' => 0,
            'points' => 0,
        ]);

        return true;
    }

    public static function playNextWeek(): bool
    {
        $fixtures = Fixture::with(['home_team', 'away_team'])
        ->nextWeek()
        ->isPlayed(false)
        ->get();

        if ($fixtures->isEmpty()) {
            throw new FixtureCompletedException('No more fixtures to play');
        }
        foreach ($fixtures as $fixture) {
            $homeTeam = $fixture->home_team;
            $awayTeam = $fixture->away_team;
            $matchMakingService = new MatchMakingService();

            $chances = $matchMakingService->calculateTeamChances($homeTeam, $awayTeam);

            $homeScore = $matchMakingService->generateBiasedScore($chances['home']);
            $awayScore = $matchMakingService->generateBiasedScore($chances['away']);

            $fixture->update([
                'home_team_score' => $homeScore,
                'away_team_score' => $awayScore,
                'is_played' => true
            ]);
        }
        return true;
    }

    public static function updateTeamPoints(int $teamId, int $teamScore, int $opponentScore): void
    {
        $team = Team::find($teamId);

        if ($teamScore > $opponentScore) {
            $team->points += 3;
            $team->won += 1;
        } elseif ($teamScore === $opponentScore) {
            $team->points += 1;
            $team->drawn += 1;
        } else {
            $team->lost += 1;
        }

        $team->played += 1;
        $team->goals_for += $teamScore;
        $team->goals_against += $opponentScore;
        $team->goals_difference = $team->goals_for - $team->goals_against;

        $team->save();
    }

    public static function updateTeamPredictions(int $teamId): void
    {
        $team = Team::find($teamId);

        $played = max(1, $team->played);

        $strengthFactor = $team->strength / 100;

        $rawScore = ((3 * $team->won) + (1 * $team->drawn) + $strengthFactor) / (3 * $played + 1);

        $prediction = round($rawScore * 100, 2);
        $prediction = min(max($prediction, 5), 95);

        $team->prediction = $prediction;
        $team->save();
    }
}