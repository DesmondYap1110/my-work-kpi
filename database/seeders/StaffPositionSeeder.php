<?php

namespace Database\Seeders;

use App\Models\StaffPosition;
use Illuminate\Database\Seeder;

class StaffPositionSeeder extends Seeder
{
    public function run(): void
    {
        // position_id=1 is load-bearing: the whole admin portal gates
        // login on position_id === 1, matching the legacy app's behaviour.
        StaffPosition::firstOrCreate(
            ['position_name' => 'Administrator'],
            ['job_scope' => 'System administrator with full access to the KPI portal.']
        );
    }
}
