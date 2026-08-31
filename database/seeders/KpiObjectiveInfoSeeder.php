<?php

namespace Database\Seeders;

use App\Models\KpiObjectiveInfo;
use Illuminate\Database\Seeder;

class KpiObjectiveInfoSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'Task Completion Quality',
            'Punctuality & Attendance',
            'Communication & Teamwork',
            'Initiative & Problem Solving',
            'Client Satisfaction',
        ] as $title) {
            KpiObjectiveInfo::firstOrCreate(['kojbInfo_title' => $title]);
        }
    }
}
