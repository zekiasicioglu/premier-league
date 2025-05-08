<?php

namespace App\Http\Controllers\Api;

use App\Facades\FixtureServiceFacade;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateFixtureRequest;
use App\Models\Fixture;
use App\Models\Team;
use App\Services\Fixtures\Exceptions\FixtureCompletedException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FixtureController extends Controller
{

    public function createFixture(CreateFixtureRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $strategy = !empty($validated['strategy']) ? $validated['strategy'] : 'round-robin';

        return response()->json([
            'success' => true,
            'data'    => FixtureServiceFacade::generateFixtures($validated['teams'], $strategy),
        ], 200);
    }

    public function showFixtures(Request $request): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data'    => FixtureServiceFacade::getFixtures(),
        ], 200);
    }

    public function getCurrentWeek(Request $request): JsonResponse
    {
        $fixtures = Fixture::with(['home_team:id,name', 'away_team:id,name'])
        ->currentWeek()
        ->get();

        $week = null;
        $is_played = false;
        $formattedFixtures = $fixtures->map(function ($fixture) use(&$week, &$is_played) {
            $week = $fixture->week;
            $is_played = $fixture->is_played;
            return Fixture::formatFixtureData($fixture);
        });

        $teamIds = $fixtures->pluck('home_team_id')
        ->merge($fixtures->pluck('away_team_id'))
        ->unique()
        ->values();

        $teams = Team::whereIn('id', $teamIds)->get();

        return response()->json([
            'success' => true,
            'week'    => $week,
            'is_played' => $is_played,
            'fixture'  => $formattedFixtures,
            'teams'    => $teams,
        ], 200);
    }

    public function resetData(): JsonResponse
    {
        try {
            return response()->json([
                'success' => FixtureServiceFacade::resetFixture(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to reset fixture.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function deleteData(): JsonResponse
    {
        try {
            return response()->json([
                'success' => FixtureServiceFacade::deleteFixture(),
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete fixture.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function playNextWeek(Request $request): JsonResponse
    {
        try {
            FixtureServiceFacade::playNextWeek();
            return response()->json([
                'success' => true,
            ], 200);

        } catch (FixtureCompletedException $e) {
            return response()->json([
                'success' => true,
                'completed' => true
            ], 200);
        }catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to play matches',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
