<?php

namespace App\Models;

use App\Observers\TeamObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;

#[ObservedBy([TeamObserver::class])]
class Team extends Model
{
    public $table = 'teams';
    protected $fillable = [
        'name',
        'city',
        'strength',
        'prediction',
        'played', 
        'won', 
        'drawn', 
        'lost', 
        'goals_for', 
        'goals_against', 
        'goals_difference', 
        'points',
    ];
}
