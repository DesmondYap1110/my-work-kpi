<?php

namespace App\Http\Controllers\Appraisal;

use App\Enums\AppraisalCycle;
use App\Enums\AssessmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Appraisal\StoreAppraisalRequest;
use App\Http\Requests\Appraisal\UpdateAppraisalRequest;
use App\Interfaces\BreadcrumbInterfaces;
use App\Models\Assessment;
use App\Models\AssessmentTemplate;
use App\Models\KpiSetting;
use App\Models\Staff;
use App\Queries\AppraisalListQuery;
use App\Services\AppraisalScheduleService;
use App\Services\AssessmentScoreService;
use App\Support\Csv;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * The appraiser's side of a performance review.
 *
 * The administrator opens an appraisal against a member and a period of their
 * choosing, rates the form, and generates it. Generating is the moment it
 * stops being a private working note and becomes feedback the member can read
 * - so it is a deliberate action of its own, not a side effect of saving.
 *
 * Everything here is administrator-only; the member's read-only half lives in
 * MyAppraisalController.
 */
class AppraisalController extends Controller implements BreadcrumbInterfaces
{
    public function __construct(private readonly AssessmentScoreService $scores)
    {
    }

    public function getBreadcrumbs(): array
    {
        $crumbs = [['name' => 'Appraisal', 'route' => 'appraisals.index', 'active' => false]];

        if (request()->routeIs('appraisals.schedule')) {
            return [['name' => 'Appraisal', 'route' => '', 'active' => false], ['name' => 'Schedule', 'route' => '', 'active' => true]];
        }

        if (request()->routeIs('appraisals.show')) {
            $crumbs[] = ['name' => 'Review Form', 'route' => '', 'active' => true];

            return $crumbs;
        }

        return [['name' => 'Appraisal', 'route' => '', 'active' => true]];
    }

    public function index(): View
    {
        return view('appraisals.index', [
            'members' => Staff::active()->excludingAdmin()->with('position')->orderBy('staff_name')->get(),
        ]);
    }

    /**
     * The Appraisal list as CSV, with the list's own filters (member, team,
     * position, status, period) - see AppraisalListQuery. Scores are the same
     * figures as each appraisal's Summary.
     */
    public function export(Request $request, AppraisalListQuery $list): StreamedResponse
    {
        // reorder(): lazyById pages by ascending id, which a "newest first"
        // order would fight with - the rows would come out incomplete.
        $query = $list->forRequest($request)->with(['staff.team', 'position', 'reviewer'])->reorder();
        $num = fn ($n) => $n === null ? null : round((float) $n, 2);

        $rows = (function () use ($query, $num) {
            // In chunks, so a long history never has to be in memory at once.
            foreach ($query->lazyById(200) as $appraisal) {
                $s = $this->scores->summary($appraisal);

                yield [
                    $appraisal->staff->staff_name ?? 'Unknown member',
                    $appraisal->staff->email ?? null,
                    $appraisal->staff->team->team_name ?? null,
                    $appraisal->position->position_name ?? null,
                    $appraisal->period_from?->format('Y-m-d'),
                    $appraisal->period_to?->format('Y-m-d'),
                    $appraisal->review_date?->format('Y-m-d'),
                    $appraisal->next_assessment_date?->format('Y-m-d'),
                    $appraisal->status->label(),
                    $num($s['projects']['percentage']),
                    $num($s['projects']['points']),
                    $num($s['objectives']['percentage']),
                    $num($s['objectives']['points']),
                    $num($s['percentage']),
                    $s['band']->label ?? null,
                    $s['band']->outcome ?? null,
                    $appraisal->reviewer->staff_name ?? null,
                    $appraisal->generated_at?->format('Y-m-d H:i'),
                    $appraisal->comments,
                ];
            }
        })();

        return Csv::download('appraisals-'.now()->format('Ymd-His').'.csv', [
            'Member', 'Email', 'Team', 'Position', 'Period from', 'Period to', 'Review date', 'Next assessment',
            'Status', 'Project %', 'Project points', 'Objectives %', 'Objective points', 'KPI score', 'Band', 'Outcome',
            'Appraiser', 'Generated at', 'Comments',
        ], $rows);
    }

    /**
     * One review form as CSV - its details, every KPI objective item with the
     * employee and reviewer marks, the project tasks behind the project marks,
     * and the summary. One file, a Section column saying which part a row is.
     */
    public function exportOne(Assessment $appraisal): StreamedResponse
    {
        $appraisal->load(['staff.team', 'position', 'reviewer', 'template.bands', 'scores']);
        $s = $this->scores->summary($appraisal);
        $num = fn ($n) => $n === null ? null : round((float) $n, 2);

        $rows = [];
        // By reference: an arrow function would add to a copy of $rows.
        $detail = function (string $label, $value) use (&$rows) {
            $rows[] = ['Details', $label, $value, null, null, null, null, null];
        };

        $detail('Member', $appraisal->staff->staff_name ?? 'Unknown member');
        $detail('Team', $appraisal->staff->team->team_name ?? null);
        $detail('Position', $appraisal->position->position_name ?? null);
        $detail('Review period', $appraisal->periodLabel());
        $detail('Review date', $appraisal->review_date?->format('Y-m-d'));
        $detail('Next assessment', $appraisal->next_assessment_date?->format('Y-m-d'));
        $detail('Status', $appraisal->status->label());
        $detail('Appraiser', $appraisal->reviewer->staff_name ?? null);

        foreach ($s['objectives']['groups'] as $group) {
            foreach ($group['rows'] as $row) {
                $rows[] = ['KPI objectives', $group['category']->name, $row['objective'], $row['info']->title,
                    $row['employee_score'], $row['reviewer_score'], $row['marks'] ? max($row['marks']) : null, null];
            }
        }

        foreach ($s['projects']['tasks'] as $task) {
            $rows[] = ['Projects', $task->project->title ?? null, $task->title, $task->tag->name ?? 'No tag',
                null, null, $num($task->points()), $task->status->label()];
        }

        $rows[] = ['Summary', 'Projects', $num($s['projects']['percentage']).($s['projects']['percentage'] === null ? '' : '%'), null, null, null, $num($s['projects']['points']), 'of '.$s['projects']['share']];
        $rows[] = ['Summary', 'KPI objectives', $num($s['objectives']['percentage']).($s['objectives']['percentage'] === null ? '' : '%'), null, null, null, $num($s['objectives']['points']), 'of '.$s['objectives']['share']];
        $rows[] = ['Summary', 'KPI score', $num($s['percentage']), null, null, null, null, 'of 100'];
        $rows[] = ['Summary', 'Band', $s['band']->label ?? null, $s['band']->outcome ?? null, null, null, null, null];
        $rows[] = ['Summary', 'Other comments', $appraisal->comments, null, null, null, null, null];

        $name = \Illuminate\Support\Str::slug($appraisal->staff->staff_name ?? 'member');

        return Csv::download('appraisal-'.$name.'-'.$appraisal->period_from?->format('Ymd').'-'.$appraisal->period_to?->format('Ymd').'.csv', [
            'Section', 'Category / Project / Field', 'Objective / Task / Value', 'Item / Tag', 'Employee mark', 'Reviewer mark', 'Best mark / Points', 'Status / Share',
        ], $rows);
    }

    /**
     * Review Schedule: each member's cycle and when they are due, as a paged,
     * filterable table - see App\Components\Datatables\ReviewScheduleList.
     */
    public function schedule(AppraisalScheduleService $schedule): View
    {
        $rows = $schedule->rows();

        return view('appraisals.schedule', [
            'members' => Staff::active()->excludingAdmin()->with('position')->orderBy('staff_name')->get(),
            'overdueCount' => $rows->where('state', 'overdue')->count(),
            'soonCount' => $rows->where('state', 'soon')->count(),
            'noticeDays' => $schedule->noticeDays(),
        ]);
    }

    /**
     * How many days before an appraisal is due the administrator is notified -
     * set on the Review Schedule page.
     */
    public function updateNotice(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'appraisal_notice_days' => ['required', 'integer', 'min:0', 'max:60'],
        ], [], ['appraisal_notice_days' => 'notice days']);

        KpiSetting::current()->update($validated);

        $days = (int) $validated['appraisal_notice_days'];

        return back()->with('status', $days === 0
            ? 'You will be notified on the day an appraisal is due.'
            : 'You will be notified '.$days.' '.\Illuminate\Support\Str::plural('day', $days).' before an appraisal is due.');
    }

    /**
     * Sets how often a member is appraised - from the Review Schedule on the
     * Appraisal page.
     */
    public function updateCycle(Request $request, Staff $staff): RedirectResponse
    {
        abort_if($staff->isAdmin(), 404);

        $validated = $request->validate([
            'appraisal_cycle' => ['required', Rule::enum(AppraisalCycle::class)],
        ]);

        $staff->update(['appraisal_cycle' => $validated['appraisal_cycle']]);

        $cycle = AppraisalCycle::from($validated['appraisal_cycle']);

        return back()->with('status', $cycle->isScheduled()
            ? $staff->staff_name.' will be appraised every '.$cycle->label().'.'
            : $staff->staff_name.' is now appraised manually - no schedule.');
    }

    public function store(StoreAppraisalRequest $request): RedirectResponse
    {
        $staff = Staff::findOrFail($request->integer('staff_id'));

        // Review Every on the New Appraisal form: a first appraisal can put the
        // member on a schedule without a trip to Appraisal > Schedule.
        if ($request->filled('appraisal_cycle') && $request->input('appraisal_cycle') !== $staff->appraisal_cycle) {
            $staff->update(['appraisal_cycle' => $request->input('appraisal_cycle')]);
        }

        $assessment = Assessment::create([
            'template_id' => AssessmentTemplate::current()->id,
            'staff_id' => $staff->id,
            // Pinned now: the form's measurements come from the role held at
            // the time of review, not whatever the member is promoted into.
            'position_id' => $staff->position_id,
            'reviewer_id' => Auth::id(),
            'period_from' => $request->date('period_from'),
            'period_to' => $request->date('period_to'),
            'review_date' => $request->date('review_date'),
            // Left blank, it follows the member's review cycle, so the next one
            // is scheduled without anyone having to work the date out.
            'next_assessment_date' => $request->date('next_assessment_date')
                ?? AppraisalCycle::tryFrom((string) $staff->appraisal_cycle)?->after($request->date('period_to')),
            'status' => AssessmentStatus::Draft,
        ]);

        return redirect()->route('appraisals.show', $assessment->id)
            ->with('status', 'Appraisal opened for '.$staff->staff_name.'.');
    }

    public function show(Assessment $appraisal): View
    {
        return view('appraisals.show', $this->formPayload($appraisal, readOnly: false));
    }

    public function update(UpdateAppraisalRequest $request, Assessment $appraisal): RedirectResponse
    {
        $this->refuseGenerated($appraisal);

        $this->applyScores($request, $appraisal);
        $periodMoved = $this->applyPeriod($request, $appraisal);

        // Save & Generate: the marks on screen are saved above, then handed to
        // the member in the same step - so nothing typed is ever left behind.
        if ($request->boolean('generate')) {
            return $this->generate($appraisal->fresh());
        }

        return back()->with('status', $periodMoved
            ? 'Appraisal saved. Project marks were recounted for the new review period.'
            : 'Appraisal saved.');
    }

    /**
     * Hands the appraisal to the member.
     *
     * Refused while nothing has been rated: an empty form tells the member
     * nothing and cannot be scored, so generating one is a mistake rather
     * than a decision.
     */
    public function generate(Assessment $appraisal): RedirectResponse
    {
        $appraisal->load(['template.bands', 'scores', 'staff', 'position']);

        if ($this->scores->summary($appraisal)['percentage'] === null) {
            return back()->withErrors(['appraisal' => 'Rate at least one item before generating this appraisal.']);
        }

        $appraisal->update([
            'status' => AssessmentStatus::Generated,
            'generated_at' => now(),
            'reviewer_id' => $appraisal->reviewer_id ?? Auth::id(),
        ]);

        return back()->with('status', 'Appraisal generated. '.($appraisal->staff->staff_name ?? 'The member').' can now read it.');
    }

    /**
     * Takes it back for more work. The member stops being able to see it, so
     * this is the counterpart of generating rather than an edit.
     */
    public function reopen(Assessment $appraisal): RedirectResponse
    {
        $appraisal->update(['status' => AssessmentStatus::Draft, 'generated_at' => null]);

        return back()->with('status', 'Appraisal reopened. It is hidden from the member again.');
    }

    public function destroy(Assessment $appraisal): RedirectResponse
    {
        $appraisal->delete();

        return back()->with('status', 'Appraisal deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function formPayload(Assessment $appraisal, bool $readOnly): array
    {
        $appraisal->load([
            'staff', 'position', 'reviewer',
            'template.bands', 'scores',
        ]);

        return [
            'appraisal' => $appraisal,
            'summary' => $this->scores->summary($appraisal),
            'readOnly' => $readOnly || $appraisal->isGenerated(),
        ];
    }

    /**
     * Returns true when the period moved, since the project marks are then
     * counted over a different stretch of work.
     */
    private function applyPeriod(UpdateAppraisalRequest $request, Assessment $appraisal): bool
    {
        $from = $request->date('period_from');
        $to = $request->date('period_to');

        $moved = ! $appraisal->period_from?->equalTo($from) || ! $appraisal->period_to?->equalTo($to);

        $appraisal->update([
            'period_from' => $from,
            'period_to' => $to,
            'review_date' => $request->date('review_date'),
            'next_assessment_date' => $request->date('next_assessment_date'),
            'comments' => $request->input('comments'),
        ]);

        return $moved;
    }

    private function applyScores(UpdateAppraisalRequest $request, Assessment $appraisal): void
    {
        // The Reviewer column only. The Employee column is the member's own
        // self-assessment - see MyAppraisalController::update() - so the
        // appraiser's save never touches it.
        $reviewer = $request->input('reviewer', []);

        // Only measurements this member's position is actually rated on: the
        // ids arrive from a form, and a stray one would attach a score to
        // somebody else's KPI item.
        foreach ($this->scores->measurementsFor($appraisal) as $info) {
            // Only a mark the item actually allows - see its allowed marks on
            // the position's KPI Setting page.
            $allowed = $this->scores->marksFor($info, $appraisal);

            $this->scores->putMark($appraisal, $info->id, 'reviewer_score', $this->mark($reviewer[$info->id] ?? null, $allowed));
        }
    }

    /**
     * Blank means unrated, and unrated is not zero - see AssessmentScore. A
     * mark the item does not allow is treated as blank rather than trusted.
     *
     * @param  array<int, int>  $allowed
     */
    private function mark($value, array $allowed): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return in_array((int) $value, $allowed, true) ? (int) $value : null;
    }

    private function refuseGenerated(Assessment $appraisal): void
    {
        abort_if($appraisal->isGenerated(), 403,
            'This appraisal has been generated. Reopen it before making changes.');
    }
}
