<?php

use App\Http\Controllers\Api\FixtureController;
use App\Http\Controllers\Api\TeamController;
use Illuminate\Support\Facades\Route;

Route::get('/teams', [TeamController::class, 'index']);
Route::post('/fixtures', [FixtureController::class, 'createFixture']);
Route::get('/fixtures', [FixtureController::class, 'showFixtures']);
Route::get('/fixtures/current', [FixtureController::class, 'getCurrentWeek']);
Route::get('/fixtures/play_next_week', [FixtureController::class, 'playNextWeek']);
Route::post('/fixtures/reset', [FixtureController::class, 'resetData']);
