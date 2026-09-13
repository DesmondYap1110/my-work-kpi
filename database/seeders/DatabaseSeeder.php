<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * What a brand-new install needs to be usable, and nothing more.
 *
 * A company starts with an administrator who can sign in and an appraisal form
 * ready to score against. Its positions, teams, members, KPIs and projects are
 * its own to enter - seeding sample ones means somebody has to find and delete
 * them before the system holds only real data.
 *
 * Set ADMIN_EMAIL and ADMIN_PASSWORD before seeding to choose the login;
 * without them the account is admin@mykpi.test with a generated password,
 * printed once.
 *
 * Not called here, on purpose:
 *  - TeamSeeder: created a "Management" team only so the administrator had
 *    something to point at. The administrator belongs to no team.
 *  - KpiObjectiveInfoSeeder: a sample "Executive" position with demo KPIs.
 *  - ConfirmationAssessmentSeeder: loads the Confirmation Assessment Form's
 *    measurements onto one named position; run it for that position once it
 *    exists.
 * All three can still be run by hand with db:seed --class=...
 */
class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            // position_id 1 is the access gate - see StaffPosition::ADMIN_ID.
            StaffPositionSeeder::class,
            AdminStaffSeeder::class,
            // The appraisal form's parts, scale and bands. Safe to re-run:
            // rows a company has reworded are matched, not replaced.
            AssessmentFormSeeder::class,
        ]);
    }
}
