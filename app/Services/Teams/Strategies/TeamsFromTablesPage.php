<?php

namespace App\Services\Teams\Strategies;

use Carbon\Carbon;
use GuzzleHttp\Client;

class TeamsFromTablesPage implements TeamsStrategyInterface
{
    public function fetchTeams(): array
    {
        $client = new Client;
        $res = $client->request('GET', 'https://footballapi.pulselive.com/football/standings?compSeasons=578&altIds=true&detail=2&FOOTBALL_COMPETITION=1');

        if ($res->getStatusCode() != 200) {
            echo "Failed to fetch teams from tables page";
            return [];
        }

        $response = json_decode($res->getBody());
        if (empty($response->tables[0]->entries)) {
            echo "Teams not found";
            return [];
        }
        $now = Carbon::now();
        return collect($response->tables[0]->entries)->map(function ($entry) use($now) {
           
            if(
                !empty($entry->team) && !empty($entry->team->name) && 
                !empty($entry->ground) && !empty($entry->ground->city) &&
                !empty($entry->overall) && isset($entry->overall->points)
            )
            {
                return [
                    'name' => $entry->team->name,
                    'city' => $entry->ground->city,
                    'strength' => $entry->overall->points,
                    'created_at' => $now,
                ];
            }     
        })->filter()->toArray();
    }
}