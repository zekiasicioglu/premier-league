<?php

namespace Tests\Unit\Services\Teams;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\Teams\TeamsService;
use App\Models\Team;
use App\Services\Teams\Strategies\TeamsStrategyInterface;
use App\Services\Teams\TeamsStrategySelector;
use Mockery;

class TeamsServiceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->teamsService = new TeamsService();
        
        $mockStrategy = Mockery::mock(TeamsStrategyInterface::class);
        $mockStrategy->shouldReceive('fetchTeams')->andReturn([
            [
                'name' => 'Test Team',
                'city' => 'Test City',
                'strength' => 80
            ]
        ]);
        
        $this->mockStrategy = $mockStrategy;
    }

    public function test_set_strategy_sets_correct_strategy()
    {
        $this->teamsService->setStrategy('teams');
        $this->assertNotNull($this->teamsService->getStrategy());
    }

    public function test_seed_teams_creates_teams()
    {
        $this->teamsService->setStrategy('teams');
        $result = $this->teamsService->seedTeams();

        $this->assertTrue($result);
        $this->assertGreaterThan(0, Team::count());
    }

    public function test_seed_teams_with_invalid_strategy_throws_exception()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->teamsService->seedTeams('invalid-strategy');
    }

    public function test_seed_teams_without_strategy_throws_exception()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->teamsService->seedTeams();
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
} 