<?php

namespace Tests\Unit\Services\Fixtures;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\Fixtures\FixtureService;
use App\Services\Fixtures\Exceptions\FixtureCompletedException;
use App\Services\Fixtures\Strategies\FixtureStrategyInterface;
use App\Models\Fixture;
use App\Models\Team;
use App\Services\MatchMaking\MatchMakingService;
use Mockery;

class FixtureServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->fixtureService = new FixtureService();
    }

    public function test_set_strategy_sets_correct_strategy()
    {
        $this->fixtureService->setStrategy('round-robin');
        $this->assertNotNull($this->fixtureService->getStrategy());
    }

    public function test_generate_fixtures_creates_fixtures()
    {
        $team1 = Team::create(['name' => 'Team A', 'city' => 'City A', 'strength' => 80]);
        $team2 = Team::create(['name' => 'Team B', 'city' => 'City B', 'strength' => 70]);

        $mockStrategy = Mockery::mock(FixtureStrategyInterface::class);
        $mockStrategy->shouldReceive('generateFixtures')->once()->andReturn([
            [
                'week' => 1,
                'home_team_id' => $team1->id,
                'away_team_id' => $team2->id
            ]
        ]);

        $this->fixtureService->setStrategy('round-robin');
        $this->fixtureService->strategy = $mockStrategy;

        $fixtures = $this->fixtureService->generateFixtures([
            ['id' => $team1->id, 'name' => 'Team A', 'city' => 'City A'],
            ['id' => $team2->id, 'name' => 'Team B', 'city' => 'City B']
        ]);

        $this->assertNotEmpty($fixtures);
        $this->assertIsArray($fixtures);
        $this->assertEquals(1, count($fixtures));
        $this->assertEquals(1, $fixtures[0]['week']);
        $this->assertEquals($team1->id, $fixtures[0]['home_team_id']);
        $this->assertEquals($team2->id, $fixtures[0]['away_team_id']);
    }

    public function test_get_fixtures_returns_formatted_fixtures()
    {
        $team1 = Team::create(['name' => 'Team A', 'city' => 'City A', 'strength' => 80]);
        $team2 = Team::create(['name' => 'Team B', 'city' => 'City B', 'strength' => 70]);

        Fixture::create([
            'week' => 1,
            'home_team_id' => $team1->id,
            'away_team_id' => $team2->id,
            'is_played' => false
        ]);

        $fixtures = FixtureService::getFixtures();

        $this->assertIsArray($fixtures);
        $this->assertNotEmpty($fixtures);
        $this->assertArrayHasKey('week', $fixtures[0]);
        $this->assertArrayHasKey('matches', $fixtures[0]);
    }

    public function test_reset_fixture_resets_all_fixtures()
    {
        $team1 = Team::create(['name' => 'Team A', 'city' => 'City A', 'strength' => 80]);
        $team2 = Team::create(['name' => 'Team B', 'city' => 'City B', 'strength' => 70]);

        Fixture::create([
            'week' => 1,
            'home_team_id' => $team1->id,
            'away_team_id' => $team2->id,
            'home_team_score' => 2,
            'away_team_score' => 1,
            'is_played' => true
        ]);

        $result = FixtureService::resetFixture();

        $this->assertTrue($result);
        $this->assertEquals(0, Fixture::first()->home_team_score);
        $this->assertEquals(0, Fixture::first()->away_team_score);
        $this->assertFalse((bool)Fixture::first()->is_played);
    }

    public function test_delete_fixture_deletes_all_fixtures()
    {
        $team1 = Team::create(['name' => 'Team A', 'city' => 'City A', 'strength' => 80]);
        $team2 = Team::create(['name' => 'Team B', 'city' => 'City B', 'strength' => 70]);

        Fixture::create([
            'week' => 1,
            'home_team_id' => $team1->id,
            'away_team_id' => $team2->id
        ]);

        $result = FixtureService::deleteFixture();

        $this->assertTrue($result);
        $this->assertEquals(0, Fixture::count());
    }

    public function test_play_next_week_throws_exception_when_no_fixtures()
    {
        $this->expectException(FixtureCompletedException::class);
        FixtureService::playNextWeek();
    }

    public function test_update_team_points_updates_correctly_for_win()
    {
        $team = Team::create([
            'name' => 'Test Team',
            'city' => 'Test City',
            'strength' => 80,
            'points' => 0,
            'won' => 0,
            'drawn' => 0,
            'lost' => 0,
            'goals_for' => 0,
            'goals_against' => 0
        ]);

        FixtureService::updateTeamPoints($team->id, 2, 1);

        $team->refresh();
        $this->assertEquals(3, $team->points);
        $this->assertEquals(1, $team->won);
        $this->assertEquals(2, $team->goals_for);
        $this->assertEquals(1, $team->goals_against);
    }

    public function test_update_team_points_updates_correctly_for_draw()
    {
        $team = Team::create([
            'name' => 'Test Team',
            'city' => 'Test City',
            'strength' => 80,
            'points' => 0,
            'won' => 0,
            'drawn' => 0,
            'lost' => 0,
            'goals_for' => 0,
            'goals_against' => 0
        ]);

        FixtureService::updateTeamPoints($team->id, 1, 1);

        $team->refresh();
        $this->assertEquals(1, $team->points);
        $this->assertEquals(1, $team->drawn);
        $this->assertEquals(1, $team->goals_for);
        $this->assertEquals(1, $team->goals_against);
    }

    public function test_update_team_points_updates_correctly_for_loss()
    {
        $team = Team::create([
            'name' => 'Test Team',
            'city' => 'Test City',
            'strength' => 80,
            'points' => 0,
            'won' => 0,
            'drawn' => 0,
            'lost' => 0,
            'goals_for' => 0,
            'goals_against' => 0
        ]);

        FixtureService::updateTeamPoints($team->id, 0, 2);

        $team->refresh();
        $this->assertEquals(0, $team->points);
        $this->assertEquals(1, $team->lost);
        $this->assertEquals(0, $team->goals_for);
        $this->assertEquals(2, $team->goals_against);
    }

    public function test_update_team_predictions_calculates_correctly()
    {
        $team = Team::create([
            'name' => 'Test Team',
            'city' => 'Test City',
            'strength' => 80,
            'won' => 2,
            'drawn' => 1,
            'played' => 3,
            'points' => 7,
            'goals_for' => 5,
            'goals_against' => 2
        ]);

        FixtureService::updateTeamPredictions($team->id);

        $team->refresh();
        $this->assertGreaterThanOrEqual(5, $team->prediction);
        $this->assertLessThanOrEqual(95, $team->prediction);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
} 