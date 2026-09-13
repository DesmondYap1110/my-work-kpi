<?php

namespace App\Http\Controllers\Appraisal;

use App\Http\Controllers\Controller;
use App\Models\Assessment;
use App\Models\AssessmentCheckin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/**
 * The monthly check-ins that precede an appraisal.
 *
 * The form carries two of them, each a period, a date, the reviewer's feedback
 * and the reviewee's reply - the conversations held during probation rather
 * than the judgement at the end of it. They carry no score; they are the
 * record of what was said at the time, which is what makes an "Extend" fair
 * rather than a surprise.
 *
 * Any number may be added, since a probation extended by three months is
 * reviewed monthly and so grows more of them.
 */
class AppraisalCheckinController extends Controller
{
    public function store(Request $request, Assessment $appraisal): RedirectResponse
    {
        $this->refuseGenerated($appraisal);

        $validated = $this->validated($request);
        $validated['assessment_id'] = $appraisal->id;
        $validated['sort_order'] = ((int) $appraisal->checkins()->max('sort_order')) + 1;

        AssessmentCheckin::create($validated);

        return back()->with('status', 'Check-in added successfully.');
    }

    public function update(Request $request, Assessment $appraisal, AssessmentCheckin $checkin): RedirectResponse
    {
        $this->refuseGenerated($appraisal);
        $this->refuseForeign($appraisal, $checkin);

        $checkin->update($this->validated($request));

        return back()->with('status', 'Check-in updated successfully.');
    }

    public function destroy(Assessment $appraisal, AssessmentCheckin $checkin): RedirectResponse
    {
        $this->refuseGenerated($appraisal);
        $this->refuseForeign($appraisal, $checkin);

        $checkin->delete();

        return back()->with('status', 'Check-in deleted successfully.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'period_from' => ['nullable', 'date'],
            'period_to' => ['nullable', 'date', 'after_or_equal:period_from'],
            'review_date' => ['nullable', 'date'],
            'reviewer_feedback' => ['nullable', 'string', 'max:5000'],
            'reviewee_comments' => ['nullable', 'string', 'max:5000'],
        ], [
            'period_to.after_or_equal' => 'A check-in cannot end before it starts.',
        ]);
    }

    /**
     * The appraisal is in the URL and the check-in's id is in the form, so the
     * two are checked against each other rather than trusted separately.
     */
    private function refuseForeign(Assessment $appraisal, AssessmentCheckin $checkin): void
    {
        abort_unless($checkin->assessment_id === $appraisal->id, 404);
    }

    private function refuseGenerated(Assessment $appraisal): void
    {
        abort_if($appraisal->isGenerated(), 403,
            'This appraisal has been generated. Reopen it before making changes.');
    }
}
