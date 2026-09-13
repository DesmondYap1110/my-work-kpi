<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Interfaces\BreadcrumbInterfaces;
use App\Models\Assessment;
use App\Services\AssessmentScoreService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * The member's side: reading a review that has been handed to them.
 *
 * Two rules, and both are enforced here rather than by hiding links. An
 * appraisal is only ever readable by the person it is about, and only once the
 * appraiser has generated it - a draft is the appraiser's working note, and
 * nobody should read their own review while it is still being decided.
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
                ->generated()
                ->orderByDesc('period_to')
                ->get(),
        ]);
    }

    public function show(Request $request, Assessment $appraisal): View
    {
        // Not "not allowed" but "not found": an appraisal a member may not read
        // should not be distinguishable from one that does not exist.
        abort_unless($appraisal->staff_id === $request->user()->id && $appraisal->isGenerated(), 404);

        $appraisal->load([
            'staff', 'position', 'reviewer',
            'template.sections', 'template.ratings', 'template.bands',
            'scores', 'projectScores.project',
        ]);

        return view('appraisals.show', [
            'appraisal' => $appraisal,
            'summary' => $this->scores->summary($appraisal),
            'ratings' => $appraisal->template->ratings,
            'readOnly' => true,
        ]);
    }
}
