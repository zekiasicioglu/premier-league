<?php

namespace App\Services\Teams\Strategies;

use Carbon\Carbon;
use GuzzleHttp\Client;

class TeamsFromTeamsPage implements TeamsStrategyInterface
{
    public function fetchTeams(): array
    {
        $client = new Client;
        $res = $client->request('GET', 'https://footballapi.pulselive.com/football/teams?pageSize=100&compSeasons=719&comps=1&altIds=true&page=0', [
            'headers' => [
                'Account' => 'premierleague',
                'Content-Type' => 'application/x-www-form-urlencoded; charset=UTF-8',
                'Referer' => 'https://www.premierleague.com/',
                'User-Agent' => 'Mozilla/5.0',
            ]
        ]);

        if ($res->getStatusCode() != 200) {
            echo "Failed to fetch teams from teams page";
            return [];
        }

        $response = json_decode($res->getBody());
        if (empty($response->content)) {
            echo "Teams not found";
            return [];
        }

        $now = Carbon::now();
        return collect($response->content)->map(function ($team) use($now) {
            return [
                'name' => $team->name ?? null,
                'city' => $team->grounds[0]->city ?? null,
                'created_at' => $now,
            ];
        })->filter()->toArray();
    }
}