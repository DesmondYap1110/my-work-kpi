<?php

use App\Models\AssessmentBand;
use App\Models\AssessmentRating;
use App\Models\AssessmentTemplate;
use Illuminate\Database\Migrations\Migration;

/**
 * Replaces the placeholder scale and bands with the ones printed on the
 * company's Confirmation Assessment Form.
 *
 * The appraisal module was built before that form was to hand, so it shipped
 * with conventional defaults and a screen for correcting them. This is that
 * correction, made once: a migration rather than a seeder edit, because
 * AssessmentFormSeeder deliberately leaves existing rows alone - a company that
 * has since reworded its own scale must not have this done to them twice.
 *
 * What the form actually says:
 *
 *   Rating   1 Poor .. 5 Outstanding, each with its own definition
 *   Bands    six, 90-100 Outstanding down to under 50 Poor
 *   Outcome  70 and above Pass, 50-69 Extend, under 50 Fail
 *            "If the score is <70%, probation will be extended for another
 *             3 months, to be reviewed on a monthly basis."
 */
return new class extends Migration
{
    private const SCALE = [
        1 => ['Poor', 'Does not meet minimum job requirements. Performance is unacceptable. Responsibilities are not being fulfilled and important objectives have not been achieved. Needs immediate improvement.'],
        2 => ['Need Improvement', 'Performance in one or more critical areas does not meet expectations. Not all planned objectives were achieved within the established standards and some responsibilities were not completely fulfilled.'],
        3 => ['Satisfied', 'All job requirements were met and planned objectives were achieved within established standards. There were no critical areas where achievements were less than planned.'],
        4 => ['Good', 'Exceeds established standards in most areas of responsibilities. All requirements were met and objectives were achieved above the established standards.'],
        5 => ['Outstanding', 'Exceptional performance in all areas of responsibilities. Planned objectives were achieved well above the established standards and accomplishments were made in unexpected areas.'],
    ];

    /**
     * [min, max, category, outcome] - the ranges as printed, and the outcome
     * each one carries under the form's three thresholds.
     */
    private const BANDS = [
        [90, 100, 'Outstanding', 'Pass'],
        [80, 89, 'Very Good', 'Pass'],
        [70, 79, 'Good', 'Pass'],
        [60, 69, 'Satisfactory', 'Extend'],
        [50, 59, 'Need Improvement', 'Extend'],
        [0, 49, 'Poor', 'Fail'],
    ];

    public function up(): void
    {
        $template = AssessmentTemplate::current();

        foreach (self::SCALE as $value => [$label, $description]) {
            AssessmentRating::updateOrCreate(
                ['template_id' => $template->id, 'value' => $value],
                ['label' => $label, 'description' => $description]
            );
        }

        // Five placeholder bands become six real ones, so they are replaced
        // rather than matched up one by one.
        AssessmentBand::where('template_id', $template->id)->delete();

        foreach (self::BANDS as [$min, $max, $label, $outcome]) {
            AssessmentBand::create([
                'template_id' => $template->id,
                'min_score' => $min,
                'max_score' => $max,
                'label' => $label,
                'outcome' => $outcome,
            ]);
        }
    }

    public function down(): void
    {
        // The placeholders this replaced were not worth keeping, and a company
        // that has edited these since would not want them back either.
    }
};
