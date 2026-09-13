<?php

namespace Database\Seeders;

use App\Models\AssessmentBand;
use App\Models\AssessmentTemplate;
use Illuminate\Database\Seeder;

/**
 * The shape of the appraisal form: its two parts, its rating scale and its
 * bands.
 *
 * ConfirmationAssessmentSeeder seeds what is rated - the categories,
 * objectives and measurements of the Confirmation Assessment Form. This seeds
 * how it is scored, which is the half a company is most likely to want in its
 * own words, so every row here is editable afterwards on the Appraisal Form
 * screen.
 *
 * Re-running is safe: existing rows are matched and left alone rather than
 * duplicated, so a company that has reworded its scale does not get the
 * defaults back.
 */
class AssessmentFormSeeder extends Seeder
{
    /**
     * [min, max, category, outcome]
     *
     * The form's Performance Band table, with the outcome each range carries
     * under its own three thresholds: 70 and above Pass, 50-69 Extend, under
     * 50 Fail. A score below 70 extends probation by three months.
     */
    private const BANDS = [
        [90, 100, 'Outstanding', 'Pass'],
        [80, 89, 'Very Good', 'Pass'],
        [70, 79, 'Good', 'Pass'],
        [60, 69, 'Satisfactory', 'Extend'],
        [50, 59, 'Need Improvement', 'Extend'],
        [0, 49, 'Poor', 'Fail'],
    ];

    public function run(): void
    {
        $template = AssessmentTemplate::current();

        foreach (self::BANDS as [$min, $max, $label, $outcome]) {
            AssessmentBand::firstOrCreate(
                ['template_id' => $template->id, 'min_score' => $min],
                ['max_score' => $max, 'label' => $label, 'outcome' => $outcome]
            );
        }
    }
}
