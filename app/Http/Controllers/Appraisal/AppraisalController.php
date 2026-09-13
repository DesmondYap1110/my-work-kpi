<?php

namespace App\Http\Controllers\Appraisal;

use App\Enums\AssessmentStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Appraisal\StoreAppraisalRequest;
use App\Http\Requests\Appraisal\UpdateAppraisalRequest;
use App\Interfaces\BreadcrumbInterfaces;
use App\Models\Assessment;
use App\Models\AssessmentTemplate;
use App\Models\Staff;
use App\Services\AssessmentScoreService;
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

    public function store(StoreAppraisalRequest $request): RedirectResponse
    {
        $staff = Staff::findOrFail($request->integer('staff_id'));

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
            'next_assessment_date' => $request->date('next_assessment_date'),
            'status' => AssessmentStatus::Draft,
        ]);

        // Part 2 is the member's own work in the period, so it can be filled in
        // the moment the period is known.
        $this->scores->syncProjectRows($assessment);

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

        // Marks first, period second. Moving the period rebuilds Part 2 with
        // fresh rows, and the marks in this request were given against the rows
        // that were on screen - recording them afterwards would look them up by
        // ids that no longer exist and quietly wipe them. Saved first, they are
        // on the old rows when syncProjectRows() carries them over by project.
        $this->applyScores($request, $appraisal);
        $periodMoved = $this->applyPeriod($request, $appraisal);

        return back()->with('status', $periodMoved
            ? 'Appraisal saved. Part 2 was rebuilt for the new review period.'
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
        $appraisal->load(['template.sections', 'template.ratings', 'template.bands', 'scores', 'projectScores']);

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
            'template.sections', 'template.ratings', 'template.bands',
            'scores', 'projectScores.project',
        ]);

        return [
            'appraisal' => $appraisal,
            'summary' => $this->scores->summary($appraisal),
            'ratings' => $appraisal->template->ratings,
            'readOnly' => $readOnly || $appraisal->isGenerated(),
        ];
    }

    /**
     * Returns true when the period moved, since that means Part 2 no longer
     * describes the work being judged and has to be rebuilt.
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

        if ($moved) {
            $this->scores->syncProjectRows($appraisal);
        }

        return $moved;
    }

    private function applyScores(UpdateAppraisalRequest $request, Assessment $appraisal): void
    {
        $employee = $request->input('employee', []);
        $reviewer = $request->input('reviewer', []);

        // Only measurements this member's position is actually rated on: the
        // ids arrive from a form, and a stray one would attach a score to
        // somebody else's KPI item.
        foreach ($this->scores->measurementsFor($appraisal) as $info) {
            $this->scores->putScore(
                $appraisal,
                $info->id,
                $this->mark($employee[$info->id] ?? null),
                $this->mark($reviewer[$info->id] ?? null),
            );
        }

        $projectEmployee = $request->input('project_employee', []);
        $projectReviewer = $request->input('project_reviewer', []);

        foreach ($appraisal->projectScores()->get() as $row) {
            $row->update([
                'employee_score' => $this->mark($projectEmployee[$row->id] ?? null),
                'reviewer_score' => $this->mark($projectReviewer[$row->id] ?? null),
            ]);
        }
    }

    /**
     * Blank means unrated, and unrated is not zero - see AssessmentScore.
     */
    private function mark($value): ?int
    {
        return $value === null || $value === '' ? null : (int) $value;
    }

    private function refuseGenerated(Assessment $appraisal): void
    {
        abort_if($appraisal->isGenerated(), 403,
            'This appraisal has been generated. Reopen it before making changes.');
    }
}
