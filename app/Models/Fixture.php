<?php

namespace App\Models;

use App\Observers\FixtureObserver;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[ObservedBy([FixtureObserver::class])]
class Fixture extends Model
{
    public $table = 'fixtures';
    protected $fillable = [
        'week',
        'home_team_id',
        'away_team_id',
        'home_team_score',
        'away_team_score',
        'is_played',
    ];

    public function home_team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    public function away_team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    public function scopeCurrentWeek(Builder $query): void
    {
        $query->where('week', Fixture::getCurrentWeekQuery());
    }

    public function scopeNextWeek(Builder $query): void
    {
        $query->where('week', Fixture::getNextWeekQuery());
    }
    public function scopeIsPlayed(Builder $query, bool $is_played): void
    {
        $query->where('is_played', $is_played);
    }

    public static function getCurrentWeekQuery(){
        return Fixture::query()
            ->selectRaw('COALESCE(MAX(CASE WHEN is_played = true THEN week END), MIN(week)) as week')
            ->value('week');
    }

    public static function getNextWeekQuery(){
        return Fixture::query()
            ->selectRaw('COALESCE(MIN(CASE WHEN is_played = false THEN week END), 0) as week')
            ->value('week');
    }

    public static function formatFixtureData(Fixture $fixture){
        return [
            'home_team_id' => $fixture->home_team_id,
            'home_team_name' => !empty($fixture->home_team) ? $fixture->home_team->name : null,
            'away_team_id' => $fixture->away_team_id,
            'away_team_name' => !empty($fixture->away_team) ? $fixture->away_team->name : null,
            'home_team_score' => $fixture->home_team_score,
            'away_team_score' => $fixture->away_team_score
        ];
    }
}
