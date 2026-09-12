<?php

namespace Database\Seeders;

use App\Models\KpiCategory;
use App\Models\KpiObjective;
use App\Models\KpiObjectiveInfo;
use App\Models\StaffPosition;
use Illuminate\Database\Seeder;

/**
 * A worked example of the KPI structure, so a fresh install has something to
 * look at rather than an empty screen:
 *
 *     category -> objective -> measurable items (with their allowed marks)
 *
 * Seeded against a sample job, never the Administrator - that position is the
 * portal's access gate and is not assessed.
 */
class KpiObjectiveInfoSeeder extends Seeder
{
    public function run(): void
    {
        $position = StaffPosition::firstOrCreate(
            ['position_name' => 'Executive'],
            ['job_scope' => 'Sample position showing how a KPI is structured.']
        );

        if ($position->isAdministrator()) {
            return;
        }

        $structure = [
            'Soft Skill' => [
                'Communication' => [
                    'Active listening',
                    'Clear and concise written updates',
                ],
                'Attitude' => [
                    'Open to constructive criticism',
                    'Proactive and takes initiative',
                ],
            ],
            'Technical Skill' => [
                'Problem Solving' => [
                    'Identifies issues and analyses root cause',
                    'Develops actionable solutions',
                ],
                'Delivery' => [
                    'Delivered on schedule',
                    'Quality of completed work',
                ],
            ],
        ];

        foreach ($structure as $categoryName => $objectives) {
            $category = KpiCategory::firstOrCreate([
                'position_id' => $position->id,
                'name' => $categoryName,
            ]);

            foreach ($objectives as $objectiveTitle => $items) {
                $objective = KpiObjective::firstOrCreate([
                    'position_id' => $position->id,
                    'category_id' => $category->id,
                    'title' => $objectiveTitle,
                ]);

                foreach ($items as $title) {
                    KpiObjectiveInfo::firstOrCreate(
                        ['objective_id' => $objective->id, 'title' => $title],
                        // A 1-5 scale, matching the rating scale on a typical
                        // confirmation assessment form.
                        ['allowed_marks' => [5, 4, 3, 2, 1]]
                    );
                }
            }
        }

    }
}
