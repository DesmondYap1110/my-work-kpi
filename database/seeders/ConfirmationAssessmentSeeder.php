<?php

namespace Database\Seeders;

use App\Models\KpiCategory;
use App\Models\KpiObjective;
use App\Models\KpiObjectiveInfo;
use App\Models\StaffPosition;
use Illuminate\Database\Seeder;

/**
 * Part 1 of the Confirmation Assessment Form, as a worked example.
 *
 *     category (Technical, Personal, ...)
 *       -> objective (Technical Knowledge, Problem Solving, ...)
 *           -> measurable items, each rated 1-5
 *
 * Every item allows the same 1-5 scale, so a group's maximum is simply five
 * times its item count - which is how the form's /30, /55, /45, /50 and /60
 * totals arise. The counts below are asserted in the test suite, so a typo
 * that changes a group's maximum gets caught.
 *
 * "Project Management" and "Leadership" are the form's "(if applicable)"
 * groups; they are seeded so the structure is complete, and a company that
 * doesn't use them can delete the category.
 */
class ConfirmationAssessmentSeeder extends Seeder
{
    /**
     * The 1-5 rating scale used throughout the form.
     */
    private const MARKS = [5, 4, 3, 2, 1];

    /**
     * category => objective => items
     */
    public static function structure(): array
    {
        return [
            'Technical' => [
                'Technical Knowledge' => [
                    'Relevant functional knowledge (knowing what to do)',
                    'Constantly acquiring new knowledge to improve job performance (knowing how best to do it)',
                ],
                'Problem Solving' => [
                    'Identify issues and analyse its root cause',
                    'Develop actionable solutions to address the specific challenges',
                    'Decision making is guided by utilising design thinking fundamentals',
                    'Displaying resourcefulness, innovation, creativity and proactiveness in problem solving',
                ],
            ],

            'Personal' => [
                'Attitude' => [
                    'Positivity, can-do attitude and enthusiasm',
                    'Open minded to constructive criticism',
                    'Adaptability to changes',
                    'Solution-focused mindset',
                    'Proactive and having initiative (ie in performing tasks, self learning)',
                    'Respectful - courteous, inclusive',
                ],
                'Work Quality' => [
                    'Compliance - adherence to company policies, rules and regulations',
                    'Organised and systematic approach to work; ability to prioritise',
                    'Responsible - care of company asset',
                    'Professional image - mannerism in dealing with external stakeholders, personal hygiene and grooming',
                    'Responsible - job accountability; follow up and follow through skills',
                ],
            ],

            'Interpersonal' => [
                'Communication' => [
                    'Active listening',
                    'Active participation in discussions',
                    'Clear and concise convey of information in both verbal and written form',
                    'Employ appropriate communication channel for different purposes and stakeholders',
                    'Effective engagement with clients',
                ],
                'Teamwork' => [
                    'Helpful',
                    'Team player - sharing and cooperative',
                    'Open and transparent communication',
                    'Having team spirit in problem solving; no blame game',
                ],
            ],

            'Project Management' => [
                'Time Management' => [
                    'Appropriate time allocation and timeline/milestone management',
                ],
                'Manpower Management' => [
                    "Appropriate manpower allocation and assignment according to staff's strength",
                    'Appropriate staff delegation',
                ],
                'Planning & Strategising' => [
                    'Plan and strategy is aligned to the goal of the project and the direction of the company',
                    'Develop actionable steps with the available resources, taking into consideration expected outcome and having contingency plan',
                ],
                'Monitoring & Controlling' => [
                    'Ensure lead products and lead promotions are up to date',
                    'Ensure 3Rs (right content, right platform, right target audience) for all ads',
                    'Timely intervention in issues arise',
                    'Internal and external conflict resolution',
                    'Process improvement',
                ],
            ],

            'Leadership' => [
                'Vision' => [
                    'Setting clear directions and defining goals',
                    'Having strategic plans',
                ],
                'Communication' => [
                    'Effective communication',
                    'Create the avenue that foster effective communication',
                ],
                'Charisma' => [
                    'Inspiring and motivating',
                    'Influential and persuasive',
                    'Foster a positive work environment, encourage collaboration and lead by example',
                    'Fairness',
                ],
                'Training' => [
                    'Guiding, coaching and mentoring staff',
                    'Creating a learning and sharing climate amongst staff',
                ],
                'Decisiveness' => [
                    'Making informed decisions backed by reliable data',
                    'Careful but bold in decision making',
                ],
            ],
        ];
    }

    public function run(?string $positionName = null): void
    {
        $position = StaffPosition::where('position_name', $positionName ?: 'Software Engineer')->first()
            ?? StaffPosition::firstOrCreate(
                ['position_name' => $positionName ?: 'Software Engineer'],
                ['job_scope' => 'Builds and maintains the product.']
            );

        // The access gate is never assessed.
        if ($position->isAdministrator()) {
            return;
        }

        foreach (self::structure() as $categoryName => $objectives) {
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
                        ['allowed_marks' => self::MARKS]
                    );
                }
            }
        }

    }
}
