<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            StaffPositionSeeder::class,
            TeamSeeder::class,
            AdminStaffSeeder::class,
            KpiObjectiveInfoSeeder::class,
            // The appraisal form's parts, scale and bands. Safe to re-run:
            // rows a company has reworded are matched, not replaced.
            AssessmentFormSeeder::class,
        ]);
    }
}
