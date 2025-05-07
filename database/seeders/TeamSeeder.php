<?php

namespace Database\Seeders;

use App\Facades\TeamsServiceFacade;
use App\Models\Team;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        if(!empty(Team::count())){
            return;
        }
        $result = TeamsServiceFacade::seedTeams("tables");
    
        echo $result ? 'Teams seeded successfully \n' : 'Failed to seed teams \n';
    }
}
