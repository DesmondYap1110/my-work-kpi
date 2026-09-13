<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Interfaces\BreadcrumbInterfaces;
use App\Models\Assessment;
use App\Services\AssessmentScoreService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The member's side of an appraisal.
 *
 * While it is a draft, the member fills in the Employee column - their own
 * self-assessment - and nothing else: the appraiser's marks, the score and
 * the comments stay hidden until the appraisal is generated. Once generated,
 * the whole review is theirs to read.
 *
 * An appraisal is only ever reachable by the person it is about, enforced here
 * rather than by hiding links.
 */
class MyAppraisalController extends Controller implements BreadcrumbInterfaces
{
    public function __construct(private readonly AssessmentScoreService $scores)
    {
    }

    public function getBreadcrumbs(): array
    {
        $crumbs = [['name' => 'My Appraisal', 'route' => 'my.appraisals.index', 'active' => false]];

        if (request()->routeIs('my.appraisals.show')) {
            $crumbs[] = ['name' => 'Review Form', 'route' => '', 'active' => true];

            return $crumbs;
        }

        return [['name' => 'My Appraisal', 'route' => '', 'active' => true]];
    }

    public function index(Request $request): View
    {
        return view('my-appraisals.index', [
            'appraisals' => Assessment::query()
                ->with(['position', 'reviewer'])
                ->forStaff($request->user()->id)
                ->orderByDesc('period_to')
                ->get(),
        ]);
    }

    public function show(Request $request, Assessment $appraisal): View
    {
        $this->refuseOthers($request, $appraisal);

        $appraisal->load([
            'staff', 'position', 'reviewer',
            'template.bands', 'scores',
        ]);

        return view('appraisals.show', [
            'appraisal' => $appraisal,
            'summary' => $this->scores->summary($appraisal),
            'readOnly' => $appraisal->isGenerated(),
            'selfAssessment' => ! $appraisal->isGenerated(),
        ]);
    }

    /**
     * Saves the member's self-assessment: the Employee column only. The
     * Reviewer column belongs to the appraiser and is never read from here.
     */
    public function update(Request $request, Assessment $appraisal): RedirectResponse
    {
        $this->refuseOthers($request, $appraisal);

        abort_if($appraisal->isGenerated(), 403,
            'This appraisal has been generated, so your self-assessment can no longer be changed.');

        $request->validate([
            'employee' => ['array'],
            'employee.*' => ['nullable', 'integer', 'min:0'],
        ]);

        $employee = $request->input('employee', []);
        $appraisal->load('position');

        // Only items this position is rated on, and only marks each item
        // allows - the ids and values arrive from a form.
        foreach ($this->scores->measurementsFor($appraisal) as $info) {
            $value = $employee[$info->id] ?? null;
            $allowed = $this->scores->marksFor($info, $appraisal);
            $mark = $value === null || $value === '' || ! in_array((int) $value, $allowed, true) ? null : (int) $value;

            $this->scores->putMark($appraisal, $info->id, 'employee_score', $mark);
        }

        return back()->with('status', 'Self-assessment saved. Your appraiser will see your marks.');
    }

    /**
     * Not "not allowed" but "not found": an appraisal a member may not open
     * should not be distinguishable from one that does not exist.
     */
    private function refuseOthers(Request $request, Assessment $appraisal): void
    {
        abort_unless($appraisal->staff_id === $request->user()->id, 404);
    }
}
