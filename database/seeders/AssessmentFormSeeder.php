<?php

namespace Database\Seeders;

use App\Models\AssessmentBand;
use App\Models\AssessmentRating;
use App\Models\AssessmentSection;
use App\Models\AssessmentTemplate;
use App\Models\KpiCategory;
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
     * value => [label, what earning it means]
     *
     * The Rater Description table from the Confirmation Assessment Form.
     */
    private const SCALE = [
        5 => ['Outstanding', 'Exceptional performance in all areas of responsibilities. Planned objectives were achieved well above the established standards and accomplishments were made in unexpected areas.'],
        4 => ['Good', 'Exceeds established standards in most areas of responsibilities. All requirements were met and objectives were achieved above the established standards.'],
        3 => ['Satisfied', 'All job requirements were met and planned objectives were achieved within established standards. There were no critical areas where achievements were less than planned.'],
        2 => ['Need Improvement', 'Performance in one or more critical areas does not meet expectations. Not all planned objectives were achieved within the established standards and some responsibilities were not completely fulfilled.'],
        1 => ['Poor', 'Does not meet minimum job requirements. Performance is unacceptable. Responsibilities are not being fulfilled and important objectives have not been achieved. Needs immediate improvement.'],
    ];

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

        $soft = $this->section($template, [
            'title' => 'Part 1 - Soft Skills',
            'type' => AssessmentSection::TYPE_RATING,
            'weightage' => 50,
            'sort_order' => 1,
        ]);

        $this->section($template, [
            'title' => 'Part 2 - Key Performance Indicator',
            'type' => AssessmentSection::TYPE_PROJECT,
            'weightage' => 50,
            'calculation' => 'tasks_in_period',
            'sort_order' => 2,
        ]);

        foreach (self::SCALE as $value => [$label, $description]) {
            AssessmentRating::firstOrCreate(
                ['template_id' => $template->id, 'value' => $value],
                ['label' => $label, 'description' => $description]
            );
        }

        foreach (self::BANDS as [$min, $max, $label, $outcome]) {
            AssessmentBand::firstOrCreate(
                ['template_id' => $template->id, 'min_score' => $min],
                ['max_score' => $max, 'label' => $label, 'outcome' => $outcome]
            );
        }

        // Every existing heading is rated under Part 1. Categories added later
        // land here too - see KpiCategoryController.
        KpiCategory::whereNull('section_id')->update(['section_id' => $soft->id]);
    }

    private function section(AssessmentTemplate $template, array $attributes): AssessmentSection
    {
        return AssessmentSection::firstOrCreate(
            ['template_id' => $template->id, 'title' => $attributes['title']],
            $attributes
        );
    }
}
