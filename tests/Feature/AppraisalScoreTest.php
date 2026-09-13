<?php

namespace Tests\Feature;

use App\Models\AssessmentBand;
use App\Models\AssessmentScore;
use App\Models\AssessmentTemplate;
use Tests\TestCase;

/**
 * What an appraisal comes to, and what it is then called.
 *
 * Both are decisions rather than arithmetic: an unrated item counting for
 * nothing, and the band that turns a score into "Extend". How projects and
 * KPI objectives combine is the KPI blend - see KpiScoreBlendTest. Each is
 * pure, so none of this needs a database.
 */
class AppraisalScoreTest extends TestCase
{
    public function test_an_unrated_item_contributes_nothing_on_either_side(): void
    {
        $unrated = new AssessmentScore(['employee_score' => null, 'reviewer_score' => null]);

        $this->assertNull($unrated->score());
    }

    /**
     * The reviewer is the appraiser: their mark is the assessment, and the
     * employee figure stands in only where they left the box empty.
     */
    public function test_the_reviewer_mark_wins_and_the_employee_mark_stands_in(): void
    {
        $this->assertSame(3, (new AssessmentScore(['employee_score' => 5, 'reviewer_score' => 3]))->score());
        $this->assertSame(5, (new AssessmentScore(['employee_score' => 5, 'reviewer_score' => null]))->score());
    }

    public function test_a_percentage_lands_in_the_band_that_covers_it(): void
    {
        $template = $this->templateWithBands();

        $this->assertSame('Very Good', $template->bandFor(85.0)?->label);
        $this->assertSame('Satisfactory', $template->bandFor(65.0)?->label);
        $this->assertSame('Poor', $template->bandFor(10.0)?->label);
    }

    /**
     * The form's own thresholds: 70 and above passes, 50-69 extends probation
     * by three months, under 50 fails.
     */
    public function test_the_outcome_follows_the_form_thresholds(): void
    {
        $template = $this->templateWithBands();

        $this->assertSame('Pass', $template->bandFor(70.0)?->outcome);
        $this->assertSame('Extend', $template->bandFor(69.99)?->outcome);
        $this->assertSame('Extend', $template->bandFor(50.0)?->outcome);
        $this->assertSame('Fail', $template->bandFor(49.99)?->outcome);

        // The worked example on the form: 25 + 30 = 55, which extends.
        $this->assertSame('Extend', $template->bandFor(55.0)?->outcome);
    }

    public function test_the_edges_of_a_band_belong_to_it(): void
    {
        $template = $this->templateWithBands();

        $this->assertSame('Outstanding', $template->bandFor(100.0)?->label);
        $this->assertSame('Outstanding', $template->bandFor(90.0)?->label);
        $this->assertSame('Very Good', $template->bandFor(89.0)?->label);
    }

    /**
     * The bands are printed as whole numbers, but a score rarely is. Reading
     * only the lower bound means there is no gap between 79 and 80 for a
     * score of 79.5 to fall into and come back unbanded.
     */
    public function test_a_fractional_score_between_two_printed_bands_still_lands(): void
    {
        $template = $this->templateWithBands();

        $this->assertSame('Good', $template->bandFor(79.5)?->label);
        $this->assertSame('Very Good', $template->bandFor(89.6)?->label);
    }

    public function test_an_unscored_appraisal_has_no_band(): void
    {
        $this->assertNull($this->templateWithBands()->bandFor(null),
            'An appraisal nobody has rated yet must not be reported as a failure.');
    }

    /**
     * The Performance Band table from the Confirmation Assessment Form.
     */
    private function templateWithBands(): AssessmentTemplate
    {
        $template = new AssessmentTemplate(['name' => 'Confirmation Assessment Form']);

        $template->setRelation('bands', collect([
            new AssessmentBand(['min_score' => 90, 'max_score' => 100, 'label' => 'Outstanding', 'outcome' => 'Pass']),
            new AssessmentBand(['min_score' => 80, 'max_score' => 89, 'label' => 'Very Good', 'outcome' => 'Pass']),
            new AssessmentBand(['min_score' => 70, 'max_score' => 79, 'label' => 'Good', 'outcome' => 'Pass']),
            new AssessmentBand(['min_score' => 60, 'max_score' => 69, 'label' => 'Satisfactory', 'outcome' => 'Extend']),
            new AssessmentBand(['min_score' => 50, 'max_score' => 59, 'label' => 'Need Improvement', 'outcome' => 'Extend']),
            new AssessmentBand(['min_score' => 0, 'max_score' => 49, 'label' => 'Poor', 'outcome' => 'Fail']),
        ]));

        return $template;
    }
}
