<?php

namespace Tests\Feature;

use Database\Seeders\ConfirmationAssessmentSeeder;
use Tests\TestCase;

/**
 * The Confirmation Assessment Form states a maximum for each group - /30 for
 * Technical, /55 for Personal and so on. Every item is rated 1-5, so those
 * maximums are five times the item count.
 *
 * That makes the totals a checkable property: if someone adds or removes a
 * measurement, the group no longer adds up and this fails, rather than the
 * form quietly scoring out of the wrong number.
 */
class ConfirmationAssessmentStructureTest extends TestCase
{
    private const MAX_RATING = 5;

    /**
     * Group => the total printed on the form.
     */
    private const FORM_TOTALS = [
        'Technical' => 30,
        'Personal' => 55,
        'Interpersonal' => 45,
        'Project Management' => 50,
        'Leadership' => 60,
    ];

    public function test_every_group_adds_up_to_the_total_on_the_form(): void
    {
        foreach (ConfirmationAssessmentSeeder::structure() as $group => $objectives) {
            $items = array_sum(array_map('count', $objectives));

            $this->assertSame(
                self::FORM_TOTALS[$group],
                $items * self::MAX_RATING,
                "Group \"{$group}\" has {$items} items, which does not match the /".self::FORM_TOTALS[$group].' printed on the form.'
            );
        }
    }

    public function test_the_structure_covers_every_group_on_the_form(): void
    {
        $this->assertSame(
            array_keys(self::FORM_TOTALS),
            array_keys(ConfirmationAssessmentSeeder::structure())
        );
    }

    public function test_no_objective_is_left_without_measurements(): void
    {
        foreach (ConfirmationAssessmentSeeder::structure() as $group => $objectives) {
            foreach ($objectives as $objective => $items) {
                $this->assertNotEmpty($items, "\"{$group} / {$objective}\" has no measurements.");
            }
        }
    }
}
